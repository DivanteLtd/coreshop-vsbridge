<?php

namespace CoreShop2VueStorefrontBundle\Tests\Bridge;

use CoreShop\Component\Core\Repository\CategoryRepositoryInterface;
use CoreShop\Component\Core\Repository\ProductRepositoryInterface;
use CoreShop2VueStorefrontBundle\Bridge\DocumentMapper\DocumentMapperFactory;
use CoreShop2VueStorefrontBundle\Bridge\ElasticsearchImporter;
use CoreShop2VueStorefrontBundle\Bridge\ImporterFactory;
use CoreShop2VueStorefrontBundle\Tests\MockeryTestCase;
use Mockery as m;
use ONGR\ElasticsearchBundle\Service\ManagerFactory;
use ONGR\ElasticsearchBundle\Service\Manager;

class ImporterFactoryTest extends MockeryTestCase
{
    /** @var ManagerFactory */
    private $managerFactory;

    /** @var DocumentMapperFactory */
    private $documentMapperFactory;

    /** @var ProductRepositoryInterface */
    private $productRepository;

    /** @var CategoryRepositoryInterface */
    private $categoryRepository;

    /** @var string[] */
    private $hosts = ['elasticsearch.local'];

    /** @var string */
    private $indexTemplate =  'some.index.template_{store}_{type}_{language}';

    private $stores = [
        'example.com' => [
            'languages' => ['en', 'es']
        ],
        'example.de' => [
            'languages' => ['de']
        ],
    ];

    protected function setUp(): void
    {
        $this->managerFactory = m::mock(ManagerFactory::class);
        $this->documentMapperFactory = m::mock(DocumentMapperFactory::class);
        $this->productRepository = m::mock(ProductRepositoryInterface::class);
        $this->categoryRepository = m::mock(CategoryRepositoryInterface::class);
    }

    /** @test **/
    public function itCreatesNoImportersByDefault(): void
    {
        $factory = new ImporterFactory($this->managerFactory, $this->documentMapperFactory, $this->productRepository, $this->categoryRepository, $this->hosts, $this->indexTemplate);
        $importers = $factory->create();

        self::assertCount(0, $importers);
    }

    /** @test **/
    public function itDoesNotAcceptInvalidStore(): void
    {
        $this->expectException(\Symfony\Component\OptionsResolver\Exception\InvalidOptionsException::class);
        $this->expectExceptionMessage('The option "store" with value "foo" is invalid. Accepted values are: null, "example.com"');

        $factory = new ImporterFactory($this->managerFactory, $this->documentMapperFactory, $this->productRepository, $this->categoryRepository, $this->hosts, $this->indexTemplate, $this->stores);
        $factory->create('foo');
    }

    /** @test **/
    public function itDoesNotAcceptInvalidLanguage(): void
    {
        $this->expectException(\Symfony\Component\OptionsResolver\Exception\InvalidOptionsException::class);
        $this->expectExceptionMessage('The option "language" with value de is invalid. Accepted values are: null, "en", "es".');

        $factory = new ImporterFactory($this->managerFactory, $this->documentMapperFactory, $this->productRepository, $this->categoryRepository, $this->hosts, $this->indexTemplate, $this->stores);
        $factory->create('example.com', 'de');
    }

    /** @test **/
    public function itDoesNotAcceptInvalidType(): void
    {
        $this->expectException(\Symfony\Component\OptionsResolver\Exception\InvalidOptionsException::class);
        $this->expectExceptionMessage('The option "type" with value "test" is invalid. Accepted values are: null, "product", "category".');

        $factory = new ImporterFactory($this->managerFactory, $this->documentMapperFactory, $this->productRepository, $this->categoryRepository, $this->hosts, $this->indexTemplate, $this->stores);
        $factory->create('example.com', 'en', 'test');
    }

    /** @test **/
    public function itCreatesImportersForAllStores(): void
    {
        $manager = m::mock(Manager::class);

        $this->managerFactory
            ->shouldReceive('createManager')
            ->once()
            ->withArgs($this->generateManagerArgs('coreshop2vuestorefront.example.com.category.en', 'some.index.template_example.com_category_en'))
            ->andReturn($manager);
        $this->managerFactory
            ->shouldReceive('createManager')
            ->once()
            ->withArgs($this->generateManagerArgs('coreshop2vuestorefront.example.com.product.en', 'some.index.template_example.com_product_en'))
            ->andReturn($manager);
        $this->managerFactory
            ->shouldReceive('createManager')
            ->once()
            ->withArgs($this->generateManagerArgs('coreshop2vuestorefront.example.com.category.es', 'some.index.template_example.com_category_es'))
            ->andReturn($manager);
        $this->managerFactory
            ->shouldReceive('createManager')
            ->once()
            ->withArgs($this->generateManagerArgs('coreshop2vuestorefront.example.com.product.es', 'some.index.template_example.com_product_es'))
            ->andReturn($manager);
        $this->managerFactory
            ->shouldReceive('createManager')
            ->once()
            ->withArgs($this->generateManagerArgs('coreshop2vuestorefront.example.de.category.de', 'some.index.template_example.de_category_de'))
            ->andReturn($manager);
        $this->managerFactory
            ->shouldReceive('createManager')
            ->once()
            ->withArgs($this->generateManagerArgs('coreshop2vuestorefront.example.de.product.de', 'some.index.template_example.de_product_de'))
            ->andReturn($manager);

        $factory = new ImporterFactory($this->managerFactory, $this->documentMapperFactory, $this->productRepository, $this->categoryRepository, $this->hosts, $this->indexTemplate, $this->stores);
        $importers = $factory->create();

        self::assertCount(6, $importers);
    }

    /** @test **/
    public function itCreatesImportersForASingleStore(): void
    {
        $manager = m::mock(Manager::class);

        $this->managerFactory
            ->shouldReceive('createManager')
            ->once()
            ->withArgs($this->generateManagerArgs('coreshop2vuestorefront.example.de.category.de', 'some.index.template_example.de_category_de'))
            ->andReturn($manager);
        $this->managerFactory
            ->shouldReceive('createManager')
            ->once()
            ->withArgs($this->generateManagerArgs('coreshop2vuestorefront.example.de.product.de', 'some.index.template_example.de_product_de'))
            ->andReturn($manager);

        $factory = new ImporterFactory($this->managerFactory, $this->documentMapperFactory, $this->productRepository, $this->categoryRepository, $this->hosts, $this->indexTemplate, $this->stores);
        $importers = $factory->create('example.de');

        self::assertCount(2, $importers);
    }

    /** @test **/
    public function itCreatesImportersForASingleStoreAndLanguage(): void
    {
        $manager = m::mock(Manager::class);

        $this->managerFactory
            ->shouldReceive('createManager')
            ->once()
            ->withArgs($this->generateManagerArgs('coreshop2vuestorefront.example.com.category.en', 'some.index.template_example.com_category_en'))
            ->andReturn($manager);
        $this->managerFactory
            ->shouldReceive('createManager')
            ->once()
            ->withArgs($this->generateManagerArgs('coreshop2vuestorefront.example.com.product.en', 'some.index.template_example.com_product_en'))
            ->andReturn($manager);

        $factory = new ImporterFactory($this->managerFactory, $this->documentMapperFactory, $this->productRepository, $this->categoryRepository, $this->hosts, $this->indexTemplate, $this->stores);
        $importers = $factory->create('example.com', 'en');

        self::assertCount(2, $importers);
    }

    /** @test **/
    public function itCreatesImportersForASingleStoreAndLanguageAndType(): void
    {
        $manager = m::mock(Manager::class);

        $this->managerFactory
            ->shouldReceive('createManager')
            ->once()
            ->withArgs($this->generateManagerArgs('coreshop2vuestorefront.example.com.product.en', 'some.index.template_example.com_product_en'))
            ->andReturn($manager);

        $factory = new ImporterFactory($this->managerFactory, $this->documentMapperFactory, $this->productRepository, $this->categoryRepository, $this->hosts, $this->indexTemplate, $this->stores);
        $importers = $factory->create('example.com', 'en', 'product');

        self::assertCount(1, $importers);
    }

    private function generateManagerArgs(string $id, string $index): array
    {
        return [
            $id,
            ['hosts' => $this->hosts, 'index_name' => $index, 'settings' => []],
            [],
            ['logger' => ['enabled' => false], 'mappings' => ['CoreShop2VueStorefrontBundle'], 'commit_mode' => 'flush', 'bulk_size' => 100]
        ];
    }
}
