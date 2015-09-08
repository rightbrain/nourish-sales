<?php
namespace Rbs\Bundle\SalesBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Rbs\Bundle\CoreBundle\Entity\Item;
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
     * @ORM\OneToOne(targetEntity="Rbs\Bundle\CoreBundle\Entity\Item")
     * @ORM\JoinColumn(name="item", nullable=false)
     */
    private $item;

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
    private $onHand;

    /**
     * @var integer
     *
     * @ORM\Column(name="on_hold", type="integer", options={"default" = 0})
     */
    private $onHold;

    /**
     * @var boolean
     *
     * @ORM\Column(name="available_on_demand", type="boolean", options={"default" = false})
     */
    private $availableOnDemand;

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
     */
    public function setOnHand($onHand)
    {
        $this->onHand = $onHand;
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
     */
    public function setOnHold($onHold)
    {
        $this->onHold = $onHold;
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
     */
    public function setItem($item)
    {
        $this->item = $item;
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
     */
    public function setAvailableOnDemand($availableOnDemand)
    {
        $this->availableOnDemand = $availableOnDemand;
    }

    /**
     * @return ArrayCollection
     */
    public function getStockHistories()
    {
        return $this->stockHistories;
    }

    /**
     * @param ArrayCollection $stockHistories
     */
    public function setStockHistories($stockHistories)
    {
        $this->stockHistories = $stockHistories;
    }
}