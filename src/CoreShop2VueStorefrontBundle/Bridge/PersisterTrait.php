<?php

namespace CoreShop2VueStorefrontBundle\Bridge;

use CoreShop\Component\Core\Model\ProductInterface;
use Symfony\Component\Console\Output\OutputInterface;

trait PersisterTrait
{
    /**
     * @param ProductInterface[] $objects
     * @param OutputInterface $output
     * @param EnginePersister $enginePersister
     */
    public function persistCollection($objects, OutputInterface $output, EnginePersister $enginePersister): void
    {
        foreach ($objects as $object) {
            try {
                $enginePersister->persist($object);
                $output->writeln(sprintf("%s %s added to index.", get_class($object), $object->getId()));
            } catch (\Exception $exception) {
                $output->writeln(
                    sprintf(
                        "There was a problem with indexing %s id %s. %s\n %s",
                        $object->getId(),
                        get_class($object),
                        $exception->getMessage(),
                        $exception->getTraceAsString()
                    )
                );
            }
            \Pimcore::collectGarbage();
        }
    }
}
