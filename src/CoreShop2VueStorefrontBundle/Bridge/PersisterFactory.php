<?php

declare(strict_types=1);

namespace CoreShop2VueStorefrontBundle\Bridge;

use ONGR\ElasticsearchBundle\Mapping\Converter;
use ONGR\ElasticsearchBundle\Mapping\IndexSettings;
use ONGR\ElasticsearchBundle\Service\IndexService;
use ONGR\ElasticsearchBundle\Service\ManagerFactory;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use CoreShop\Component\Store\Repository\StoreRepositoryInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @internal
 */
class PersisterFactory
{
    private $elasticsearchConfig;
    private $stores;
    private $storeAware;

    /**
     * @var Converter
     */
    private $converter;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var DocumentMapperFactoryInterface
     */
    private $documentMapperFactory;

    /**
     * @var RepositoryProvider
     */
    private $repositoryProvider;

    /**
     * @var StoreRepositoryInterface
     */
    private $storeRepository;

    /**
     * @var OptionsResolver
     */
    private $resolver;


    public function __construct(Converter $converter, EventDispatcherInterface $eventDispatcher, DocumentMapperFactoryInterface $documentMapperFactory, RepositoryProvider $repositoryProvider, StoreRepositoryInterface $storeRepository, array $elasticsearchConfig, array $stores = [], bool $storeAware = false)
    {
        $this->converter = $converter;
        $this->eventDispatcher = $eventDispatcher;

        $this->documentMapperFactory = $documentMapperFactory;
        $this->repositoryProvider = $repositoryProvider;
        $this->storeRepository = $storeRepository;

        $this->elasticsearchConfig = $elasticsearchConfig;
        $this->stores = $stores;
        $this->storeAware = $storeAware;
        $this->resolver = $this->configureOptions(new OptionsResolver());
    }

    /**
     * @return array<array{persister: PersisterFactory, store: string, language: string, type: string}>
     */
    public function create(?string $store = null, ?string $language = null, ?string $type = null): array
    {
        $options = $this->resolver->resolve(['store' => $store, 'language' => $language, 'type' => $type]);

        $stores = (array) ($options['store'] ?? array_keys($this->stores));

        $persisters = [];
        foreach ($stores as $name) {
            $store = $this->stores[$name];

            if ($this->storeAware) {
                $concreteStore = $this->storeRepository->findOneBy(['name' => $name]);
            }

            $languages = (array) ($options['language'] ?? $store['languages']);
            foreach ($languages as $language) {
                $types = (array) ($options['type'] ?? $this->repositoryProvider->getAliases());

                foreach ($types as $type) {
                    $variables = ['store' => $name, 'language' => $language, 'type' => $type];

                    $repository = $this->repositoryProvider->getForAlias($type);
                    $className = $this->documentMapperFactory->getDocumentClass($repository->getClassName());

                    $indexName = $this->inject($this->elasticsearchConfig['index'], $variables);
                    $settings = new IndexSettings(
                        $className,
                        $indexName,
                        $indexName,
                        $this->elasticsearchConfig['templates'][$className] ?? [],
                        $this->inject($this->elasticsearchConfig['hosts'], $variables)
                    );
                    $indexService = new IndexService($className, $this->converter, $this->eventDispatcher, $settings);
                    $persisters[] = [
                        'persister' => new EnginePersister($indexService, $this->documentMapperFactory, $language),
                        'store' => $name,
                        'language' => $language,
                        'type' => $type,
                        'repository' => $this->repositoryProvider->getForAlias($type),
                        'concreteStore' => $concreteStore
                    ];
                }
            }
        }

        return $persisters;
    }

    private function configureOptions(OptionsResolver $resolver): OptionsResolver
    {
        $stores = $this->stores;

        $resolver->setDefined(['store', 'language', 'type']);

        $resolver->setAllowedValues('store', array_merge([null], array_keys($stores)));

        $resolver->setAllowedTypes('language', ['null' ,'string']);
        $resolver->setNormalizer('language', function (Options $options, $language) use ($stores) {
            $store = $options['store'];
            if ($language === null || $store === null) {
                return null;
            }

            $storeLanguages = $stores[$store]['languages'];
            if (!in_array($language, $storeLanguages, true)) {
                $message = sprintf(
                    'The option "language" with value %s is invalid. Accepted values are: null, "%s".',
                    $language,
                    implode('", "', $storeLanguages)
                );

                throw new InvalidOptionsException($message);
            }

            return $language;
        });

        $resolver->setAllowedValues('type', array_merge([null], $this->repositoryProvider->getAliases()));

        return $resolver;
    }

    /**
     * @param array|string $template
     * @param array        $variables
     *
     * @return array|string
     */
    private function inject($template, array $variables)
    {
        if (is_array($template)) {
            foreach ($template as $idx => $item) {
                $template[$idx] = $this->inject($item, $variables);
            }

            return $template;
        }

        $keys = array_map(function(string $key) {
            return '{'. $key .'}';
        }, array_keys($variables));

        return str_replace($keys, array_values($variables), $template);
    }
}
