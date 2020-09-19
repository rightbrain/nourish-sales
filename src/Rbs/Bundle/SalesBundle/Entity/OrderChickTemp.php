<?php
namespace Rbs\Bundle\SalesBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Rbs\Bundle\CoreBundle\Entity\Depo;
use Rbs\Bundle\CoreBundle\Entity\ItemType;
use Rbs\Bundle\CoreBundle\Entity\Location;
use Symfony\Component\Validator\Constraints as Assert;
use Knp\DoctrineBehaviors\Model as ORMBehaviors;
use Xiidea\EasyAuditBundle\Annotation\ORMSubscribedEvents;

/**
 * OrderChickTemp
 *
 * @ORM\Table(name="sales_orders_chick_temp")
 * @ORM\Entity(repositoryClass="Rbs\Bundle\SalesBundle\Repository\OrderChickTempRepository")
 */
class OrderChickTemp
{
    const ORDER_TYPE_FEED = 'FEED';
    const ORDER_TYPE_CHICK = 'CHICK';

    use ORMBehaviors\Timestampable\Timestampable,
//        ORMBehaviors\SoftDeletable\SoftDeletable,
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
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Rbs\Bundle\SalesBundle\Entity\OrderItemChickTemp", mappedBy="order", cascade={"persist", "remove"}, orphanRemoval=true))
     */
    private $orderItems;

    /**
     * @var Agent
     *
     * @ORM\ManyToOne(targetEntity="Rbs\Bundle\SalesBundle\Entity\Agent")
     * @ORM\JoinColumn(name="agent_id")
     */
    private $agent;

    /**
     * @var Location
     *
     * @ORM\ManyToOne(targetEntity="Rbs\Bundle\CoreBundle\Entity\Location")
     * @ORM\JoinColumn(name="location_id", nullable=true)
     */
    private $location;

    /**
     * @var array $type
     *
     * @ORM\Column(name="order_type", type="string", length=255, columnDefinition="ENUM('FEED', 'CHICK')")
     */
    private $orderType=self::ORDER_TYPE_FEED;

    /**
     * @var float
     *
     * @ORM\Column(name="total_amount", type="float")
     */
    private $totalAmount = 0 ;

    /**
     * @var float
     *
     * @ORM\Column(name="paid_amount", type="float")
     */
    private $paidAmount = 0 ;

    /**
     * @var string
     *
     * @ORM\Column(name="order_via", type="string", length=250, nullable=true)
     */
    private $orderVia = 'SYSTEM';

    /**
     * @var Depo
     *
     * @ORM\ManyToOne(targetEntity="Rbs\Bundle\CoreBundle\Entity\Depo")
     * @ORM\JoinColumn(name="depo_id", nullable=true)
     */
    private $depo;
    
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

    public function __construct()
    {
        $this->orderItems = new ArrayCollection();
    }

    /**
     * @param \Rbs\Bundle\SalesBundle\Entity\OrderItemChickTemp $order
     */
    public function addOrder($order)
    {
        if (!$this->getOrderItems()->contains($order)) {
            $order->setOrder($this);
            $this->getOrderItems()->add($order);
        }
    }

    /**
     * @param \Rbs\Bundle\SalesBundle\Entity\OrderItemChickTemp $order
     */
    public function removeOrder($order)
    {
        if ($this->getOrderItems()->contains($order)) {
            $this->getOrderItems()->removeElement($order);
        }
    }

    public function addOrderItem(OrderItemChickTemp $item)
    {
        if (!$this->orderItems->contains($item)) {
            $this->orderItems->add($item);
        }

        return $this;
    }

    public function removeOrderItem(OrderItemChickTemp $item)
    {
        $item->setOrder(null);
        $this->orderItems->removeElement($item);
    }

    /**
     * @return ArrayCollection
     */
    public function getOrderItems()
    {
        return $this->orderItems;
    }

    /**
     * @return float
     */
    public function getTotalAmount()
    {
        return $this->totalAmount;
    }

    /**
     * @param float $totalAmount
     */
    public function setTotalAmount($totalAmount)
    {
        $this->totalAmount = $totalAmount;
    }

    /**
     * @return float
     */
    public function getPaidAmount()
    {
        return $this->paidAmount;
    }

    /**
     * @param float $paidAmount
     */
    public function setPaidAmount($paidAmount)
    {
        $this->paidAmount = $paidAmount;
    }

    /**
     * @return Agent
     */
    public function getAgent()
    {
        return $this->agent;
    }

    /**
     * @param Agent $agent
     */
    public function setAgent($agent)
    {
        $this->agent = $agent;
    }

    /** @return float */
    public function getItemsTotalAmount()
    {
        $total = 0;
        /** @var OrderItemChickTemp $item */
        foreach($this->orderItems as $item) {
            $total += $item->getTotalAmount();
        }

        return $total;
    }

    /** @return float */
    public function getOrderItemsTotalQuantity()
    {
        $total = 0;
        /** @var OrderItemChickTemp $item */
        foreach($this->orderItems as $item) {
            $total += $item->getQuantity();
        }

        return $total;
    }

    public function getOrderItemType()
    {
        foreach ($this->orderItems as $item){
            if($item->getItem()->getItemType()->getItemType() == ItemType::Chick){
                return false;
            }
        }

        return true;
    }

    /**
     * @return Location
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * @param Location $location
     */
    public function setLocation($location)
    {
        $this->location = $location;
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
     */
    public function setDepo($depo)
    {
        $this->depo = $depo;
    }

    /**
     * @return array
     */
    public function getOrderType()
    {
        return $this->orderType;
    }

    /**
     * @param array $orderType
     */
    public function setOrderType($orderType)
    {
        $this->orderType = $orderType?$orderType:self::ORDER_TYPE_FEED;
    }


    public function calculateOrderAmount()
    {
        /** @var OrderItemChickTemp $orderItems */
        foreach ($this->getOrderItems() as $orderItems) {
            $orderItems->setOrder($this);
            $orderItems->calculateTotalAmount(true);
        }
        $this->setTotalAmount($this->getItemsTotalAmount());
    }

}