<?php /** @noinspection PhpUndefinedClassInspection */

namespace CoreShop2VueStorefrontBundle\EventListener;

use CoreShop\Component\Core\Model\CategoryInterface;
use CoreShop\Component\Core\Model\ProductInterface;
use CoreShop2VueStorefrontBundle\Bridge\EnginePersister;
use CoreShop2VueStorefrontBundle\Bridge\PersisterFactory;
use CoreShop2VueStorefrontBundle\Bridge\RepositoryProvider;
use Exception;
use Pimcore\Event\Model\DataObjectEvent;
use Pimcore\Model\DataObject\AbstractObject;
use Pimcore\Model\DataObject\Concrete;
use Psr\Log\LoggerInterface;

class ProductListener
{
    /**
     * @var PersisterFactory
     */
    private $persisterFactory;

    /**
     * @var RepositoryProvider
     */
    private $repositoryProvider;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(PersisterFactory $persisterFactory, RepositoryProvider $repositoryProvider, LoggerInterface $logger)
    {
        $this->persisterFactory = $persisterFactory;
        $this->repositoryProvider = $repositoryProvider;
        $this->logger = $logger;
    }

    public function postSave(DataObjectEvent $event)
    {
        try {
            /** @var ProductInterface|CategoryInterface|Concrete $object */
            $object = $event->getObject();

            if ($this->shouldSynchronizeWithVue($object)) {
                if ($object->getType() == AbstractObject::OBJECT_TYPE_VARIANT) {
                    return false;
                }

                foreach ($this->persisterFactory->create(null, $this->repositoryProvider->getAliasFor($object)) as $config) {
                    $config['persister']->persist($object);
                }
            }
        } catch (Exception $exception) {
            $this->logger->info(sprintf(
                "Can't add object to vue index. Error %s",
                $exception->getMessage()
            ));
        }
    }

    private function shouldSynchronizeWithVue($object): bool
    {
        return $object instanceof CategoryInterface
            || $object instanceof ProductInterface;
    }
}
