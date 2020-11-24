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
    private $stores;
    private $storeAware;

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

    public function __construct(ContainerInterface $container, Converter $converter, EventDispatcherInterface $eventDispatcher, DocumentMapperFactoryInterface $documentMapperFactory, RepositoryProvider $repositoryProvider, StoreRepositoryInterface $storeRepository, array $elasticsearchConfig, array $stores = [], bool $storeAware = false)
    {
        $this->container = $container;
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
    public function create(?string $store = null, ?string $type = null, ?string $language = null, ?string $currency = null): array
    {
        $options = $this->resolver->resolve(['store' => $store, 'type' => $type, 'language' => $language, 'currency' => $currency]);

        $stores = (array) ($options['store'] ?? array_keys($this->stores));

        $persisters = [];
        foreach ($stores as $name) {
            $store = $this->stores[$name];

            if ($this->storeAware) {
                $concreteStore = $this->storeRepository->findOneBy(['name' => $name]);
            }

            $types = (array) ($options['type'] ?? $this->repositoryProvider->getAliases());
            foreach ($types as $type) {
                $languages = (array) ($options['language'] ?? $store['languages']);
                foreach ($languages as $language) {
                    $currencies = (array) ($options['currency'] ?? $store['currencies']);

                    foreach ($currencies as $currency) {
                        $repository = $this->repositoryProvider->getForAlias($type);
                        $className = $this->documentMapperFactory->getDocumentClass($repository->getClassName());

                        /** @var IndexService $indexService */
                        $indexService = $this->container->get($className);
                        $indexSettings = $indexService->getIndexSettings();

                        $variables = [
                            'store' => $name,
                            'type' => $indexSettings->getIndexName() ?? $type,
                            'language' => $language,
                            'currency' => $currency,
                        ];

                        $indexName = $this->inject($this->elasticsearchConfig['index'], $variables);
                        $settings = new IndexSettings(
                            $className,
                            $indexName,
                            $indexName,
                            $this->elasticsearchConfig['templates'][$className] ?? [],
                            $this->inject($this->elasticsearchConfig['hosts'], $variables)
                        );
                        $indexSettings = new IndexSettings(
                            $className,
                            $indexName,
                            $indexName,
                            array_replace_recursive(
                                $indexSettings->getIndexMetadata(),
                                $this->elasticsearchConfig['templates'][$className] ?? []
                            ),
                            $this->inject($this->elasticsearchConfig['hosts'], $variables, true)
                        );
                        $indexService = new IndexService(
                            $className,
                            $this->converter,
                            $this->eventDispatcher,
                            $indexSettings
                        );
                        $persisters[] = [
                            'persister' => new EnginePersister($indexService, $this->documentMapperFactory, $language),
                            'store' => $name,
                            'type' => $type,
                            'language' => $language,
                            'currency' => $currency,
                            'repository' => $this->repositoryProvider->getForAlias($type),
                            'concreteStore' => $concreteStore
                        ];
                    }
                }
            }
        }

        return $persisters;
    }

    private function configureOptions(OptionsResolver $resolver): OptionsResolver
    {
        $stores = $this->stores;

        $resolver->setDefined(['store', 'type', 'language', 'currency']);

        $resolver->setAllowedValues('store', array_merge([null], array_keys($stores)));

        $resolver->setAllowedValues('type', array_merge([null], $this->repositoryProvider->getAliases()));

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

        $resolver->setAllowedTypes('currency', ['null' ,'string']);
        $resolver->setNormalizer('currency', function (Options $options, $currency) use ($stores) {
            $store = $options['store'];
            if ($currency === null || $store === null) {
                return null;
            }

            $storeCurrencies = $stores[$store]['currencies'];
            if (!in_array($currency, $storeCurrencies, true)) {
                $message = sprintf(
                    'The option "currency" with value %s is invalid. Accepted values are: null, "%s".',
                    $currency,
                    implode('", "', $storeCurrencies)
                );

                throw new InvalidOptionsException($message);
            }

            return $currency;
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
