<?php
namespace Rbs\Bundle\SalesBundle\EventListener;


use Doctrine\ORM\EntityManager;
use Rbs\Bundle\CoreBundle\Event\ItemEvent;
use Rbs\Bundle\CoreBundle\Event\WarehouseEvent;
use Rbs\Bundle\SalesBundle\Entity\Stock;

class StockCreateListener
{
    /**
     * @var EntityManager
     */
    protected $em;

    public function __construct(EntityManager $entityManager)
    {
        $this->em = $entityManager;
    }

    public function onItemCreated(ItemEvent $itemEvent)
    {
        foreach ($this->em->getRepository('RbsCoreBundle:Warehouse')->findAll() as $warehouse) {
            $item = $itemEvent->getItem();

            $stock = new Stock();
            $stock->setItem($item);
            $stock->setWarehouse($warehouse);
            $this->em->persist($stock);
            $this->em->flush($stock);
        }
    }

    public function onWarehouseCreated(WarehouseEvent $warehouseEvent)
    {
        foreach ($this->em->getRepository('RbsCoreBundle:Item')->findAll() as $item) {
            $warehouse = $warehouseEvent->getWarehouse();

            $stock = new Stock();
            $stock->setItem($item);
            $stock->setWarehouse($warehouse);
            $this->em->persist($stock);
            $this->em->flush($stock);
        }
    }
}