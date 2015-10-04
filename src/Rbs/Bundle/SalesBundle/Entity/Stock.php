<?php
namespace Rbs\Bundle\SalesBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Rbs\Bundle\CoreBundle\Entity\Item;
use Rbs\Bundle\CoreBundle\Entity\Warehouse;
use Symfony\Component\Validator\Constraints as Assert;
use Knp\DoctrineBehaviors\Model as ORMBehaviors;
use Xiidea\EasyAuditBundle\Annotation\ORMSubscribedEvents;

/**
 * Stock
 *
 * @ORM\Table(name="stocks")
 * @ORM\Entity(repositoryClass="Rbs\Bundle\SalesBundle\Repository\StockRepository")
 * @ORMSubscribedEvents()
 */
class Stock
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
     * @var Item
     *
     * @ORM\ManyToOne(targetEntity="Rbs\Bundle\CoreBundle\Entity\Item")
     * @ORM\JoinColumn(name="item_id", nullable=false)
     */
    private $item;

    /**
     * @var Warehouse
     *
     * @ORM\ManyToOne(targetEntity="Rbs\Bundle\CoreBundle\Entity\Warehouse")
     * @ORM\JoinColumn(name="warehouse_id", nullable=false)
     */
    private $warehouse;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Rbs\Bundle\SalesBundle\Entity\StockHistory", mappedBy="stock")
     */
    private $stockHistories;

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

    /**
     * @var boolean
     *
     * @ORM\Column(name="available_on_demand", type="boolean", options={"default" = false})
     */
    private $availableOnDemand = 0;

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
     * @return boolean
     */
    public function isAvailableOnDemand()
    {
        return $this->availableOnDemand;
    }

    /**
     * @param boolean $availableOnDemand
     * @return $this
     */
    public function setAvailableOnDemand($availableOnDemand)
    {
        $this->availableOnDemand = $availableOnDemand;

        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getStockHistories()
    {
        return $this->stockHistories;
    }

    /**
     * @return Warehouse
     */
    public function getWarehouse()
    {
        return $this->warehouse;
    }

    /**
     * @param Warehouse $warehouse
     *
     * @return Stock
     */
    public function setWarehouse($warehouse)
    {
        $this->warehouse = $warehouse;

        return $this;
    }

    /**
     * @param ArrayCollection $stockHistories
     * @return $this
     */
    public function setStockHistories($stockHistories)
    {
        $this->stockHistories = $stockHistories;

        return $this;
    }

    public function isStockAvailable($quantity = 0)
    {
        if ($this->item && $this->item->isDeleted()) {
            return false;
        }

        if ($this->isAvailableOnDemand()) {
            return true;
        }

        return $quantity <= ($this->getOnHand() - $this->getOnHold());
    }
}