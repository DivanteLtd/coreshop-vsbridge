<?php

namespace CoreShop2VueStorefrontBundle\Command;

use CoreShop\Component\Core\Repository\CategoryRepositoryInterface;
use CoreShop\Component\Core\Repository\ProductRepositoryInterface;
use CoreShop2VueStorefrontBundle\Bridge\EnginePersister;
use CoreShop2VueStorefrontBundle\Bridge\PersisterTrait;
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
    /** @var ProductRepositoryInterface */
    private $repository;
    /** @var CategoryRepositoryInterface */
    private $categoryRepository;

    protected function configure()
    {
        $this
            ->addOption('type', null, InputOption::VALUE_REQUIRED, 'Object types to index, available: category, product')
            ->setName('vsbridge:index-objects')
            ->setDescription('Indexing objects of given type in vuestorefront');
    }

    public function __construct(
        EnginePersister $enginePersister,
        ProductRepositoryInterface $repository,
        CategoryRepositoryInterface $categoryRepository
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
            $objects = $this->repository->findAll();
        } elseif ($input->getOption('type') === self::CATEGORY) {
            $objects = $this->categoryRepository->findAll();
        }

        if (false === empty($objects)) {
            $this->persistCollection($objects, $output, $this->enginePersister);
        }
    }
}
