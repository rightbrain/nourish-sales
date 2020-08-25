<?php
namespace Rbs\Bundle\SalesBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Rbs\Bundle\CoreBundle\Entity\Item;
use Symfony\Component\Validator\Constraints as Assert;
use Knp\DoctrineBehaviors\Model as ORMBehaviors;

/**
 * OrderItemAmendmentHistory
 *
 * @ORM\Table(name="sales_order_items_amendment_history")
 * @ORM\Entity(repositoryClass="Rbs\Bundle\SalesBundle\Repository\OrderItemAmendmentHistoryRepository")
 */
class OrderItemAmendmentHistory
{
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
     * @var Item
     *
     * @ORM\ManyToOne(targetEntity="Rbs\Bundle\CoreBundle\Entity\Item")
     * @ORM\JoinColumn(name="amendment_item_id", nullable=true)
     */
    private $amendmentItem;

    /**
     * @var Delivery
     *
     * @ORM\ManyToOne(targetEntity="Rbs\Bundle\SalesBundle\Entity\Delivery")
     * @ORM\JoinColumn(name="delivery_id", nullable=true)
     */
    private $delivery;

    /**
     * @var integer
     *
     * @ORM\Column(name="quantity", type="integer")
     */
    private $quantity = 0;

    /**
     * @var integer
     *
     * @ORM\Column(name="amendment_quantity", type="integer")
     */
    private $amendmentQuantity = 0;

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

    /**
     * @return Delivery
     */
    public function getDelivery()
    {
        return $this->delivery;
    }

    /**
     * @param Delivery $delivery
     */
    public function setDelivery($delivery)
    {
        $this->delivery = $delivery;
    }

    /**
     * @return Item
     */
    public function getAmendmentItem()
    {
        return $this->amendmentItem;
    }

    /**
     * @param Item $amendmentItem
     */
    public function setAmendmentItem($amendmentItem)
    {
        $this->amendmentItem = $amendmentItem;
    }

    /**
     * @return int
     */
    public function getAmendmentQuantity()
    {
        return $this->amendmentQuantity;
    }

    /**
     * @param int $amendmentQuantity
     */
    public function setAmendmentQuantity($amendmentQuantity)
    {
        $this->amendmentQuantity = $amendmentQuantity;
    }



}