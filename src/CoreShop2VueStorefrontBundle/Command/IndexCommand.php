<?php

namespace CoreShop2VueStorefrontBundle\Command;

use CoreShop\Component\Core\Repository\CategoryRepositoryInterface;
use CoreShop\Component\Core\Repository\ProductRepositoryInterface;
use CoreShop\Component\Pimcore\BatchProcessing\BatchListing;
use CoreShop2VueStorefrontBundle\Bridge\ImporterFactory;
use Pimcore\Console\AbstractCommand;
use Pimcore\Model\DataObject\Listing;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\StyleInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class IndexCommand extends AbstractCommand
{
    protected static $defaultName = 'vsbridge:index-objects';

    /**
     * @var ImporterFactory
     */
    private $importerFactory;

    protected function configure()
    {
        $this
            ->addArgument('store', InputArgument::OPTIONAL, 'Store to index')
            ->addArgument('type', InputArgument::OPTIONAL, 'Object types to index, available: category, product')
            ->addArgument('language', InputArgument::OPTIONAL, 'Language to index')
            ->setName('vsbridge:index-objects')
            ->setDescription('Indexing objects of given type in vuestorefront');
    }

    public function __construct(ImporterFactory $importerFactory)
    {
        parent::__construct(self::$defaultName);

        $this->importerFactory = $importerFactory;
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

        $store = $input->getArgument('store');
        $language = $input->getArgument('language');
        $type = $input->getArgument('type');

        $importers = $this->importerFactory->create($store, $language, $type);
        foreach ($importers as $importer) {
            $style->section(sprintf('Importing: %1$s', $importer->describe()));

            $count = $importer->count();
            if ($count === 0) {
                $style->warning('Nothing to import, skipping.');

                continue;
            }

            $style->note(sprintf('Found %1$d items to import.', $count));
            $progressBar = $style->createProgressBar($count);
            $importer->import(function (object $object) use ($progressBar) {
                $progressBar->setMessage($object->getPath());
                $progressBar->advance();
            });
            $progressBar->clear();

            $style->success(sprintf('Imported %1$d items.', $count));
        }

        $style->success('Done.');

        return 0;
    }
}
