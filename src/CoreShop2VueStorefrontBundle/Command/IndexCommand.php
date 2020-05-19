<?php

namespace CoreShop2VueStorefrontBundle\Command;

use CoreShop\Component\Core\Repository\CategoryRepositoryInterface;
use CoreShop\Component\Core\Repository\ProductRepositoryInterface;
use CoreShop\Component\Pimcore\BatchProcessing\BatchListing;
use CoreShop2VueStorefrontBundle\Bridge\EnginePersister;
use CoreShop2VueStorefrontBundle\Bridge\PersisterTrait;
use Pimcore\Console\AbstractCommand;
use Pimcore\Model\DataObject\Listing;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\StyleInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class IndexCommand extends AbstractCommand
{
    private const PRODUCT = 'product';
    private const CATEGORY = 'category';

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
        $style = new SymfonyStyle($input, $output);
        $style->title('Coreshop => Vue Storefront data importer');

        $type = $input->getOption('type');

        if (null !== $type) {
            switch ($type) {
                case self::CATEGORY:
                    $this->importCategories($style);
                    break;
                case self::PRODUCT:
                    $this->importProducts($style);
                    break;
                default:
                    throw new InvalidArgumentException('Unexpected type');
            }

            return 0;
        }

        $this->importCategories($style);
        $this->importProducts($style);

        return 0;
    }

    private function importCategories(StyleInterface $style): void
    {
        $this->import($style, self::CATEGORY, $this->categoryRepository->getList());
    }

    private function importProducts(StyleInterface $style): void
    {
        $this->import($style, self::PRODUCT, $this->repository->getList());
    }

    private function import(StyleInterface $style, string $type, Listing $list): void
    {
        $style->section(sprintf('Importing: %1$s', $type));

        $count = $list->count();

        if ($count === 0) {
            $style->warning('Nothing to import, skipping.');
            
            return;
        }

        $style->note(sprintf('Found %1$d items to import.', $count));

        $listing = new BatchListing($list, 100);
        $progressBar = $style->createProgressBar($count);
        foreach ($listing as $object) {
            $progressBar->setMessage($object->getPath());
            $this->enginePersister->persist($object);
            $progressBar->advance();
        }
        $progressBar->clear();
        
        $style->success(sprintf('Imported %1$d items.', $count));
    }
}
