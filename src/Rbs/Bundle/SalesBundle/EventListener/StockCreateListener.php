<?php
namespace Rbs\Bundle\SalesBundle\EventListener;


use Doctrine\ORM\EntityManager;
use Rbs\Bundle\CoreBundle\Event\ItemEvent;
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

    public function onCreated(ItemEvent $itemEvent)
    {
        $item = $itemEvent->getItem();

        $stock = new Stock();
        $stock->setItem($item);
        $this->em->persist($stock);
        $this->em->flush($stock);
    }
}