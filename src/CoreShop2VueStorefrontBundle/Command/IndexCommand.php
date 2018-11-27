<?php

namespace CoreShop2VueStorefrontBundle\Command;

use CoreShop2VueStorefrontBundle\Bridge\EnginePersister;
use CoreShop2VueStorefrontBundle\Bridge\PersisterTrait;
use CoreShop2VueStorefrontBundle\Repository\CategoryRepository;
use CoreShop2VueStorefrontBundle\Repository\ProductRepository;
use Pimcore\Console\AbstractCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class IndexCommand extends AbstractCommand
{
    use PersisterTrait;

    const PRODUCT = 'product';
    const CATEGORY = 'category';

    /** @var EnginePersister */
    private $enginePersister;
    /** @var ProductRepository */
    private $repository;
    /** @var CategoryRepository */
    private $categoryRepository;

    protected function configure()
    {
        $this
            ->addOption('type', null, InputOption::VALUE_REQUIRED, 'Object types to index')
            ->setName('vue:index-objects')
            ->setDescription('Indexing objects of given type in vuestorefront');
    }

    public function __construct(
        EnginePersister $enginePersister,
        ProductRepository $repository,
        CategoryRepository $categoryRepository
    ) {
        parent::__construct();

        $this->enginePersister = $enginePersister;
        $this->repository = $repository;
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $objects = [];
        if ($input->getOption('type') === self::PRODUCT) {
            $objects = $this->repository->fetchAll();
        } elseif ($input->getOption('type') === self::CATEGORY) {
            $objects = $this->categoryRepository->fetchAll();
        }

        if (false === empty($objects)) {
            $this->persistCollection($objects, $output, $this->enginePersister);
        }
    }
}
