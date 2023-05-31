<?php
namespace Rbs\Bundle\SalesBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Rbs\Bundle\CoreBundle\Entity\Item;
use Symfony\Component\Validator\Constraints as Assert;
use Knp\DoctrineBehaviors\Model as ORMBehaviors;

/**
 * OrderItem
 *
 * @ORM\Table(name="sales_order_items")
 * @ORM\Entity(repositoryClass="Rbs\Bundle\SalesBundle\Repository\OrderItemRepository")
 */
class OrderItem
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var Order
     *
     * @ORM\ManyToOne(targetEntity="Rbs\Bundle\SalesBundle\Entity\Order", inversedBy="orderItems", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="order_id", nullable=true, onDelete="CASCADE")
     */
    private $order;

    /**
     * @var Item
     *
     * @ORM\ManyToOne(targetEntity="Rbs\Bundle\CoreBundle\Entity\Item")
     * @ORM\JoinColumn(name="item_id", nullable=false)
     * @Assert\NotBlank()
     */
    private $item;

    /**
     * @var integer
     *
     * @ORM\Column(name="quantity", type="integer")
     */
    private $quantity = 0;

    /**
     * @var integer
     *
     * @ORM\Column(name="previous_quantity", type="integer")
     */
    private $previousQuantity = 0;
    /**
     * @var integer
     *
     * @ORM\Column(name="damage_quantity", type="integer")
     */
    private $damageQuantity = 0;
    /**
     * @var integer
     *
     * @ORM\Column(name="bonus_quantity", type="integer")
     */
    private $bonusQuantity = 0;

    /**
     * @var float
     *
     * @ORM\Column(name="price", type="float")
     */
    private $price = 0;

    /**
     * @var float
     *
     * @ORM\Column(name="mrp_price", type="float")
     */
    private $mrpPrice = 0;

    /**
     * @var float
     *
     * @ORM\Column(name="total_amount", type="float")
     */
    private $totalAmount = 0;

    /**
     * @var float
     *
     * @ORM\Column(name="paid_amount", type="float")
     */
    private $paidAmount = 0;

    /**
     * @var float
     *
     * @ORM\Column(name="po_transport_incentive_amount", type="float", nullable=true)
     */
    private $poTransportIncentiveAmount;

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
     * @return int
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * @param int $quantity
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;
    }

    /**
     * @return int
     */
    public function getPreviousQuantity()
    {
        return $this->previousQuantity;
    }

    /**
     * @param int $previousQuantity
     */
    public function setPreviousQuantity($previousQuantity)
    {
        $this->previousQuantity = $previousQuantity;
    }

    /**
     * @return int
     */
    public function getDamageQuantity()
    {
        return $this->damageQuantity;
    }

    /**
     * @param int $damageQuantity
     */
    public function setDamageQuantity($damageQuantity)
    {
        $this->damageQuantity = $damageQuantity;
    }

    /**
     * @return int
     */
    public function getBonusQuantity()
    {
        return $this->bonusQuantity;
    }

    /**
     * @param int $bonusQuantity
     */
    public function setBonusQuantity($bonusQuantity)
    {
        $this->bonusQuantity = $bonusQuantity;
    }

    /**
     * @return float
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @param float $price
     */
    public function setPrice($price)
    {
        $this->price = $price;
    }

    /**
     * @return float
     */
    public function getMrpPrice()
    {
        return $this->mrpPrice;
    }

    /**
     * @param float $mrpPrice
     */
    public function setMrpPrice($mrpPrice)
    {
        $this->mrpPrice = $mrpPrice;
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
     * @return Order
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @param Order $order
     */
    public function setOrder($order)
    {
        $this->order = $order;
    }

    public function calculateTotalAmount($isSetTotalAmount = false)
    {
        $amount = $this->getQuantity() * $this->getPrice();
        if ($isSetTotalAmount) {
            $this->setTotalAmount($amount);
        }

        return $amount;
    }

    public function getDueAmount()
    {
        return ($this->getTotalAmount() - $this->getPaidAmount());
    }

    public function getDeliveredQuantity()
    {
        return ($this->getQuantity() - $this->getPaidAmount());
    }

    public function calculateBonusQuantity(){
      return  $this->getQuantity()/$this->getItem()->getPacketWeight();
    }

    /**
     * @return float
     */
    public function getPoTransportIncentiveAmount()
    {
        return $this->poTransportIncentiveAmount;
    }

    /**
     * @param float $poTransportIncentiveAmount
     */
    public function setPoTransportIncentiveAmount($poTransportIncentiveAmount)
    {
        $this->poTransportIncentiveAmount = $poTransportIncentiveAmount;
    }


}