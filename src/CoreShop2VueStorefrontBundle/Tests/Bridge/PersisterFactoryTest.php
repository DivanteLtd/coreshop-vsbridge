<?php

namespace CoreShop2VueStorefrontBundle\Tests\Bridge;

use CoreShop2VueStorefrontBundle\Bridge\DocumentMapperFactory;
use CoreShop2VueStorefrontBundle\Bridge\PersisterFactory;
use CoreShop2VueStorefrontBundle\Tests\MockeryTestCase;
use Mockery as m;
use ONGR\ElasticsearchBundle\Service\ManagerFactory;
use ONGR\ElasticsearchBundle\Service\Manager;

class PersisterFactoryTest extends MockeryTestCase
{
    /** @var ManagerFactory */
    private $managerFactory;

    /** @var DocumentMapperFactory */
    private $documentMapperFactory;

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
    }

    /** @test **/
    public function itCreatesNoPersistersByDefault(): void
    {
        $factory = new PersisterFactory($this->managerFactory, $this->documentMapperFactory, $this->hosts, $this->indexTemplate);
        $persisters = $factory->create();

        self::assertCount(0, $persisters);
    }

    /** @test **/
    public function itDoesNotAcceptInvalidStore(): void
    {
        $this->expectException(\Symfony\Component\OptionsResolver\Exception\InvalidOptionsException::class);
        $this->expectExceptionMessage('The option "store" with value "foo" is invalid. Accepted values are: null, "example.com"');

        $factory = new PersisterFactory($this->managerFactory, $this->documentMapperFactory, $this->hosts, $this->indexTemplate, $this->stores);
        $factory->create('foo');
    }

    /** @test **/
    public function itDoesNotAcceptInvalidLanguage(): void
    {
        $this->expectException(\Symfony\Component\OptionsResolver\Exception\InvalidOptionsException::class);
        $this->expectExceptionMessage('The option "language" with value de is invalid. Accepted values are: null, "en", "es".');

        $factory = new PersisterFactory($this->managerFactory, $this->documentMapperFactory, $this->hosts, $this->indexTemplate, $this->stores);
        $factory->create('example.com', 'de');
    }

    /** @test **/
    public function itDoesNotAcceptInvalidType(): void
    {
        $this->expectException(\Symfony\Component\OptionsResolver\Exception\InvalidOptionsException::class);
        $this->expectExceptionMessage('The option "type" with value "test" is invalid. Accepted values are: null, "product", "category".');

        $factory = new PersisterFactory($this->managerFactory, $this->documentMapperFactory, $this->hosts, $this->indexTemplate, $this->stores);
        $factory->create('example.com', 'en', 'test');
    }

    /** @test **/
    public function itCreatesPersistersForAllStores(): void
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

        $factory = new PersisterFactory($this->managerFactory, $this->documentMapperFactory, $this->hosts, $this->indexTemplate, $this->stores);
        $persisters = $factory->create();

        self::assertCount(6, $persisters);
    }

    /** @test **/
    public function itCreatesPersistersForASingleStore(): void
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

        $factory = new PersisterFactory($this->managerFactory, $this->documentMapperFactory, $this->hosts, $this->indexTemplate, $this->stores);
        $persisters = $factory->create('example.de');

        self::assertCount(2, $persisters);
    }

    /** @test **/
    public function itCreatesPersistersForASingleStoreAndLanguage(): void
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

        $factory = new PersisterFactory($this->managerFactory, $this->documentMapperFactory, $this->hosts, $this->indexTemplate, $this->stores);
        $persisters = $factory->create('example.com', 'en');

        self::assertCount(2, $persisters);
    }

    /** @test **/
    public function itCreatesPersistersForASingleStoreAndLanguageAndType(): void
    {
        $manager = m::mock(Manager::class);

        $this->managerFactory
            ->shouldReceive('createManager')
            ->once()
            ->withArgs($this->generateManagerArgs('coreshop2vuestorefront.example.com.product.en', 'some.index.template_example.com_product_en'))
            ->andReturn($manager);

        $factory = new PersisterFactory($this->managerFactory, $this->documentMapperFactory, $this->hosts, $this->indexTemplate, $this->stores);
        $Persisters = $factory->create('example.com', 'en', 'product');

        self::assertCount(1, $Persisters);
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
