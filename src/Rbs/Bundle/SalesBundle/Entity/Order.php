<?php
namespace Rbs\Bundle\SalesBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Knp\DoctrineBehaviors\Model as ORMBehaviors;
use Xiidea\EasyAuditBundle\Annotation\ORMSubscribedEvents;

/**
 * Order
 *
 * @ORM\Table(name="orders")
 * @ORM\Entity(repositoryClass="Rbs\Bundle\SalesBundle\Repository\OrderRepository")
 * @ORMSubscribedEvents()
 */
class Order
{
    const ORDER_STATE_PENDING = 'PENDING';
    const ORDER_STATE_HOLD = 'HOLD';
    const ORDER_STATE_COMPLETE = 'COMPLETE';
    const ORDER_STATE_CANCEL = 'CANCEL';
    const ORDER_STATE_PROCESSING = 'PROCESSING';

    const PAYMENT_STATE_PENDING = 'PENDING';
    const PAYMENT_STATE_PARTIALLY_PAID = 'PARTIALLY_PAID';
    const PAYMENT_STATE_PAID = 'PAID';
    const PAYMENT_STATE_APPROVAL = 'APPROVAL';

    const DELIVERY_STATE_PENDING = 'PENDING';
    const DELIVERY_STATE_HOLD = 'HOLD';
    const DELIVERY_STATE_READY = 'READY';
    const DELIVERY_STATE_PARTIALLY_SHIPPED = 'PARTIALLY_SHIPPED';
    const DELIVERY_STATE_SHIPPED = 'SHIPPED';

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
     * @ORM\ManyToMany(targetEntity="Payment", mappedBy="orders")
     **/
    protected $payments;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Rbs\Bundle\SalesBundle\Entity\OrderItem", mappedBy="order", cascade={"persist", "remove"}, orphanRemoval=true))
     */
    private $orderItems;

    /**
     * @var Customer
     *
     * @ORM\ManyToOne(targetEntity="Rbs\Bundle\SalesBundle\Entity\Customer")
     * @ORM\JoinColumn(name="customer", nullable=false)
     */
    private $customer;

    /**
     * @var array $type
     *
     * @ORM\Column(name="delivery_state", type="string", length=255, columnDefinition="ENUM('PENDING', 'HOLD', 'READY', 'PARTIALLY_SHIPPED', 'SHIPPED')", nullable=true)
     */
    private $deliveryState;

    /**
     * @var array $type
     *
     * @ORM\Column(name="payment_state", type="string", length=255, columnDefinition="ENUM('PENDING', 'PARTIALLY_PAID', 'PAID', 'APPROVAL')", nullable=true)
     */
    private $paymentState;

    /**
     * @var array $type
     *
     * @ORM\Column(name="order_state", type="string", length=255, columnDefinition="ENUM('PENDING', 'HOLD', 'PROCESSING', 'COMPLETE', 'CANCEL')", nullable=true)
     */
    private $orderState;

    /**
     * @var float
     *
     * @ORM\Column(name="total_amount", type="float", options={"default" = 0}, nullable=true)
     */
    private $totalAmount;

    /**
     * @var float
     *
     * @ORM\Column(name="paid_amount", type="float", options={"default" = 0}, nullable=true)
     */
    private $paidAmount;

    /**
     * @var string
     *
     * @ORM\Column(name="order_via", type="string", length=250, nullable=true)
     */
    private $orderVia = 'SYSTEM';

    /**
     * @ORM\OneToOne(targetEntity="Sms", mappedBy="order", cascade={"persist"})
     */
    protected $refSMS;

    /**
     * @var string
     *
     * @ORM\Column(name="remarks", type="text", nullable=true)
     */
    private $remark;

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
     * @param \Rbs\Bundle\SalesBundle\Entity\OrderItem $order
     */
    public function addOrder($order)
    {
        if (!$this->getOrderItems()->contains($order)) {
            $order->setOrder($this);
            $this->getOrderItems()->add($order);
        }
    }

    /**
     * @param \Rbs\Bundle\SalesBundle\Entity\OrderItem $order
     */
    public function removeOrder($order)
    {
        if ($this->getOrderItems()->contains($order)) {
            $this->getOrderItems()->removeElement($order);
        }
    }

    public function addOrderItem(OrderItem $item)
    {
        if (!$this->orderItems->contains($item)) {
            $this->orderItems->add($item);
        }

        return $this;
    }

    public function removeOrderItem(OrderItem $item)
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
     * @return array
     */
    public function getDeliveryState()
    {
        return $this->deliveryState;
    }

    /**
     * @param array $deliveryState
     */
    public function setDeliveryState($deliveryState)
    {
        $this->deliveryState = $deliveryState;
    }

    /**
     * @return array
     */
    public function getPaymentState()
    {
        return $this->paymentState;
    }

    /**
     * @param array $paymentState
     */
    public function setPaymentState($paymentState)
    {
        $this->paymentState = $paymentState;
    }

    /**
     * @return array
     */
    public function getOrderState()
    {
        return $this->orderState;
    }

    /**
     * @param array $orderState
     */
    public function setOrderState($orderState)
    {
        $this->orderState = $orderState;
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
     * @return Customer
     */
    public function getCustomer()
    {
        return $this->customer;
    }

    /**
     * @param Customer $customer
     */
    public function setCustomer($customer)
    {
        $this->customer = $customer;
    }

    /**
     * @return string
     */
    public function getOrderVia()
    {
        return $this->orderVia;
    }

    /**
     * @param string $orderVia
     */
    public function setOrderVia($orderVia)
    {
        $this->orderVia = $orderVia;
    }

    /**
     * @return string
     */
    public function getRemark()
    {
        return $this->remark;
    }

    /**
     * @param string $remark
     */
    public function setRemark($remark)
    {
        $this->remark = $remark;
    }

    /**
     * @return Sms
     */
    public function getRefSMS()
    {
        return $this->refSMS;
    }

    /**
     * @param mixed $refSMS
     */
    public function setRefSMS($refSMS)
    {
        $this->refSMS = $refSMS;
    }

    /** @return float */
    public function getItemsTotalAmount()
    {
        $total = 0;
        /** @var OrderItem $item */
        foreach($this->orderItems as $item) {
            $total += $item->getTotalAmount();
        }

        return $total;
    }

    public function isPending()
    {
        $state = false;
        if($this->orderState == 'PENDING'){
            $state = true;
        }

        return $state;
    }

    public function isComplete()
    {
        $state = false;
        if($this->orderState == 'COMPLETE'){
            $state = true;
        }

        return $state;
    }

    public function isCancel()
    {
        $state = false;
        if($this->orderState == 'CANCEL'){
            $state = true;
        }

        return $state;
    }

    /**
     * @return mixed
     */
    public function getPayments()
    {
        return $this->payments;
    }

    /**
     * @param mixed $payments
     */
    public function setPayments($payments)
    {
        $this->payments = $payments;
    }
}