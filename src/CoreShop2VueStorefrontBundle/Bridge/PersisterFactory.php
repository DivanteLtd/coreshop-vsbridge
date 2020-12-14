<?php

declare(strict_types=1);

namespace CoreShop2VueStorefrontBundle\Bridge;

use ONGR\ElasticsearchBundle\Mapping\Converter;
use ONGR\ElasticsearchBundle\Mapping\IndexSettings;
use ONGR\ElasticsearchBundle\Service\IndexService;
use ONGR\ElasticsearchBundle\Service\ManagerFactory;
use Psr\Container\ContainerInterface;
use Sami\Renderer\Index;
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
    private $sites;

    /**
     * @var ContainerInterface
     */
    private $container;

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

    public function __construct(ContainerInterface $container, Converter $converter, EventDispatcherInterface $eventDispatcher, DocumentMapperFactoryInterface $documentMapperFactory, RepositoryProvider $repositoryProvider, StoreRepositoryInterface $storeRepository, array $elasticsearchConfig, array $sites = [])
    {
        $this->container = $container;
        $this->converter = $converter;
        $this->eventDispatcher = $eventDispatcher;

        $this->documentMapperFactory = $documentMapperFactory;
        $this->repositoryProvider = $repositoryProvider;
        $this->storeRepository = $storeRepository;

        $this->elasticsearchConfig = $elasticsearchConfig;
        $this->sites = $sites;
        $this->resolver = $this->configureOptions(new OptionsResolver());
    }

    /**
     * @return array<array{persister: PersisterFactory, store: string, language: string, type: string}>
     */
    public function create(?string $site = null, ?string $type = null, ?string $language = null, ?string $store = null): array
    {
        $options = $this->resolver->resolve(['site' => $site, 'type' => $type, 'language' => $language, 'store' => $store]);

        $sites = (array) ($options['site'] ?? array_keys($this->sites));

        $persisters = [];
        foreach ($sites as $name) {
            $site = $this->sites[$name];

            $types = (array) ($options['type'] ?? $this->repositoryProvider->getAliases());
            foreach ($types as $type) {
                $languages = (array) ($options['language'] ?? $site['languages']);
                foreach ($languages as $language) {
                    $stores = (array) ($options['store'] ?? $site['stores']);

                    foreach ($stores as $store) {
                        $concreteStore = $this->storeRepository->findOneBy(['name' => $store]);
                        if ($concreteStore === null) {
                            throw new \LogicException('Invalid store name '. $store);
                        }

                        $repository = $this->repositoryProvider->getForAlias($type);
                        $className = $this->documentMapperFactory->getDocumentClass($repository->getClassName());

                        /** @var IndexService $indexService */
                        $indexService = $this->container->get($className);
                        $indexSettings = $indexService->getIndexSettings();

                        $variables = [
                            'site' => $name,
                            'type' => $indexSettings->getIndexName() ?? $type,
                            'language' => $language,
                            'currency' => $concreteStore->getCurrency()->getISOCode(),
                            'store' => $store,
                        ];

                        $indexName = $this->inject($this->elasticsearchConfig['index'], $variables);
                        $settings = new IndexSettings(
                            $className,
                            $indexName,
                            $indexName,
                            $this->elasticsearchConfig['templates'][$className] ?? [],
                            $this->inject($this->elasticsearchConfig['hosts'], $variables, true)
                        );
                        $indexSettings = new IndexSettings(
                            $className,
                            $indexName,
                            $indexName,
                            array_replace_recursive(
                                $indexSettings->getIndexMetadata(),
                                $this->elasticsearchConfig['templates'][$className] ?? []
                            ),
                            $this->inject($this->elasticsearchConfig['hosts'], $variables)
                        );
                        $indexService = new IndexService(
                            $className,
                            $this->converter,
                            $this->eventDispatcher,
                            $indexSettings
                        );

                        $persisters[] = [
                            'persister' => new EnginePersister($indexService, $this->documentMapperFactory, $language, $concreteStore),
                            'site' => $name,
                            'type' => $type,
                            'language' => $language,
                            'store' => $concreteStore,
                            'repository' => $this->repositoryProvider->getForAlias($type),
                        ];
                    }
                }
            }
        }

        return $persisters;
    }

    private function configureOptions(OptionsResolver $resolver): OptionsResolver
    {
        $sites = $this->sites;

        $resolver->setDefined(['site', 'type', 'language', 'store']);

        $resolver->setAllowedValues('site', array_merge([null], array_keys($sites)));

        $resolver->setAllowedValues('type', array_merge([null], $this->repositoryProvider->getAliases()));

        $resolver->setAllowedTypes('language', ['null' ,'string']);
        $resolver->setNormalizer('language', function (Options $options, $language) use ($sites) {
            $site = $options['site'];
            if ($language === null || $site === null) {
                return null;
            }

            $storeLanguages = $sites[$site]['languages'];
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

        $resolver->setAllowedTypes('store', ['null' ,'string']);
        $resolver->setNormalizer('store', function (Options $options, $store) use ($sites) {
            $site = $options['site'];
            if ($store === null || $site === null) {
                return null;
            }

            $stores = $sites[$site]['stores'];
            if (!in_array($store, $stores, true)) {
                $message = sprintf(
                    'The option "store" with value %s is invalid. Accepted values are: null, "%s".',
                    $store,
                    implode('", "', $stores)
                );

                throw new InvalidOptionsException($message);
            }

            return $store;
        });

        return $resolver;
    }

    /**
     * @param array|string $template
     * @param array        $variables
     *
     * @return array|string
     */
    private function inject($template, array $variables, bool $validate = false)
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
        $variables = array_combine($keys, array_map(function(string $value) {
            return strtolower($value);
        }, array_values($variables)));
        if ($validate) {
            foreach ($variables as $key => $value) {
                if (false === strpos($template, $key) && $value !== null) {
                    throw new \LogicException(sprintf('Placeholder "%1$s" missing in index "%2$s"', $key, $template));
                }
            }
        }

        return str_replace(array_keys($variables), array_values($variables), $template);
    }
}
