<?php
namespace Rbs\Bundle\SalesBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Rbs\Bundle\CoreBundle\Entity\Item;
use Rbs\Bundle\CoreBundle\Entity\Depo;
use Symfony\Component\Validator\Constraints as Assert;
use Knp\DoctrineBehaviors\Model as ORMBehaviors;
use Xiidea\EasyAuditBundle\Annotation\ORMSubscribedEvents;

/**
 * DailyDepotStock
 *
 * @ORM\Table(name="sales_daily_hatchery_stocks")
 * @ORM\Entity(repositoryClass="Rbs\Bundle\SalesBundle\Repository\DailyDepotStockRepository")
 * @ORMSubscribedEvents()
 */
class DailyDepotStock
{
    use ORMBehaviors\Timestampable\Timestampable,
        ORMBehaviors\SoftDeletable\SoftDeletable,
        ORMBehaviors\Blameable\Blameable;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var DailyDepotStockTransferred
     *
     * @ORM\OneToMany(targetEntity="Rbs\Bundle\SalesBundle\Entity\DailyDepotStockTransferred", mappedBy="dailyDepotStock")
     */
    private $dailyDepotStockTransferred;


    /**
     * @var Item
     *
     * @ORM\ManyToOne(targetEntity="Rbs\Bundle\CoreBundle\Entity\Item")
     * @ORM\JoinColumn(name="item_id", nullable=false)
     */
    private $item;

    /**
     * @var Depo
     *
     * @ORM\ManyToOne(targetEntity="Rbs\Bundle\CoreBundle\Entity\Depo")
     * @ORM\JoinColumn(name="depo_id", nullable=false)
     */
    private $depo;

    /**
     * @var integer
     *
     * @ORM\Column(name="on_hand", type="integer", options={"default" = 0})
     */
    private $onHand = 0;

    /**
     * @var integer
     *
     * @ORM\Column(name="on_hold", type="integer", options={"default" = 0})
     */
    private $onHold = 0;


    public function __construct()
    {
        $this->dailyDepotStockTransferred = new ArrayCollection();
    }

    public function addDailyDepotStockTransferred(DailyDepotStockTransferred $item)
    {
        if (!$this->dailyDepotStockTransferred->contains($item)) {
            $this->dailyDepotStockTransferred->add($item);
        }

        return $this;
    }

    public function removeDailyDepotStockTransferred(DailyDepotStockTransferred $item)
    {
        $item->setDailyDepotStock(null);
        $this->dailyDepotStockTransferred->removeElement($item);
    }
    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    public function __toString()
    {
        return (string)$this->getId();
    }

    /**
     * @return int
     */
    public function getOnHand()
    {
        return $this->onHand;
    }

    /**
     * @param int $onHand
     * @return $this
     */
    public function setOnHand($onHand)
    {
        $this->onHand = $onHand;

        return $this;
    }

    /**
     * @return int
     */
    public function getOnHold()
    {
        return $this->onHold;
    }

    /**
     * @param int $onHold
     * @return $this
     */
    public function setOnHold($onHold)
    {
        $this->onHold = $onHold;

        return $this;
    }

    /**
     * @return Item
     */
    public function getItem()
    {
        return $this->item;
    }

    /**
     * @param Item $item
     * @return $this
     */
    public function setItem($item)
    {
        $this->item = $item;

        return $this;
    }

    /**
     * @return Depo
     */
    public function getDepo()
    {
        return $this->depo;
    }

    /**
     * @param Depo $depo
     *
     * @return DailyDepotStock
     */
    public function setDepo($depo)
    {
        $this->depo = $depo;

        return $this;
    }

    /**
     * @return DailyDepotStockTransferred
     */
    public function getDailyDepotStockTransferred()
    {
        return $this->dailyDepotStockTransferred;
    }

    /**
     * @param DailyDepotStockTransferred $dailyDepotStockTransferred
     */
    public function setDailyDepotStockTransferred($dailyDepotStockTransferred)
    {
        $this->dailyDepotStockTransferred = $dailyDepotStockTransferred;
    }

    /** @return float */
    public function getTotalTransferredQuantity()
    {
        $total = 0;
        /** @var DailyDepotStockTransferred $item */
        foreach($this->dailyDepotStockTransferred as $item) {
            $total += $item->getTransferredQuantity();
        }

        return $total;
    }


    /** @return float */
    public function getTotalReceivedQuantity()
    {
        $total = 0;
        /** @var DailyDepotStockTransferred $item */
        foreach($this->dailyDepotStockTransferred as $item) {
            $total += $item->getReceivedQuantity();
        }

        return $total;
    }

    public function getRemainingQuantity(){
        return $this->getOnHand()+$this->getTotalReceivedQuantity()-$this->getOnHold()-$this->getTotalTransferredQuantity();
    }

}