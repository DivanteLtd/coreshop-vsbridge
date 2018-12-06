<?php /** @noinspection PhpUndefinedClassInspection */

namespace CoreShop2VueStorefrontBundle\EventListener;

use CoreShop\Component\Core\Model\CategoryInterface;
use CoreShop\Component\Core\Model\ProductInterface;
use CoreShop2VueStorefrontBundle\Bridge\EnginePersister;
use Exception;
use ONGR\ElasticsearchBundle\Service\Manager;
use Pimcore\Event\Model\DataObjectEvent;
use Pimcore\Model\DataObject\AbstractObject;
use Pimcore\Model\DataObject\Concrete;
use Psr\Log\LoggerInterface;

class ProductListener
{
    /** @var Manager */
    private $enginePersister;
    /** @var LoggerInterface */
    private $logger;

    public function __construct(EnginePersister $enginePersister, LoggerInterface $logger)
    {
        $this->enginePersister = $enginePersister;
        $this->logger = $logger;
    }

    public function postSave(DataObjectEvent $event)
    {
        try {
            /** @var ProductInterface|CategoryInterface|Concrete $object */
            $object = $event->getObject();
            if ($this->shouldSynchronizeWithVue($object)) {
                if (!$object->isPublished() || $object->getType() == AbstractObject::OBJECT_TYPE_VARIANT) {
                    return false;
                }
                $this->enginePersister->persist($object);
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
