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
 * Order
 *
 * @ORM\Table(name="sales_orders")
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
    const PAYMENT_STATE_CREDIT_APPROVAL = 'CREDIT_APPROVAL';

    const DELIVERY_STATE_PENDING = 'PENDING';
    const DELIVERY_STATE_HOLD = 'HOLD';
    const DELIVERY_STATE_READY = 'READY';
    const DELIVERY_STATE_PARTIALLY_SHIPPED = 'PARTIALLY_SHIPPED';
    const DELIVERY_STATE_SHIPPED = 'SHIPPED';

    const VEHICLE_STATE_IN = 'VEHICLE_IN';
    const VEHICLE_STATE_OUT = 'VEHICLE_OUT';
    const VEHICLE_STATE_PARTIALLY_SHIPPED = 'PARTIALLY_SHIPPED';

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
     * @ORM\OneToOne(targetEntity="OrderIncentiveFlag", mappedBy="order", cascade={"persist"})
     */
    protected $orderIncentiveFlag;

    /**
     * @ORM\ManyToMany(targetEntity="Payment", mappedBy="orders", cascade={"persist", "remove"}, orphanRemoval=true)
     * @ORM\OrderBy({"createdAt" = "ASC"})
     **/
    protected $payments;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Rbs\Bundle\SalesBundle\Entity\OrderItem", mappedBy="order", cascade={"persist", "remove"}, orphanRemoval=true))
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
     * @ORM\Column(name="delivery_state", type="string", length=255, columnDefinition="ENUM('PENDING', 'HOLD', 'READY', 'PARTIALLY_SHIPPED', 'SHIPPED')", nullable=true)
     */
    private $deliveryState;

    /**
     * @var array $type
     *
     * @ORM\Column(name="vehicle_state", type="string", length=255, columnDefinition="ENUM('VEHICLE_IN', 'VEHICLE_OUT', 'PARTIALLY_SHIPPED')", nullable=true)
     */
    private $vehicleState;

    /**
     * @var array $type
     *
     * @ORM\Column(name="payment_state", type="string", length=255, columnDefinition="ENUM('PENDING', 'PARTIALLY_PAID', 'PAID', 'CREDIT_APPROVAL')", nullable=true)
     */
    private $paymentState;

    /**
     * @var array $type
     *
     * @ORM\Column(name="order_state", type="string", length=255, columnDefinition="ENUM('PENDING', 'HOLD', 'PROCESSING', 'COMPLETE', 'CANCEL')", nullable=true)
     */
    private $orderState;

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
     * @ORM\Column(name="total_approved_amount", type="float")
     */
    private $totalApprovedAmount = 0 ;

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
     * @var string
     *
     * @ORM\Column(name="error_message", type="text", nullable=true)
     */
    private $errorMessage;

    /**
     *
     * @ORM\ManyToMany(targetEntity="Delivery", mappedBy="orders")
     */
    private $deliveries;

    /**
     * @var Depo
     *
     * @ORM\ManyToOne(targetEntity="Rbs\Bundle\CoreBundle\Entity\Depo")
     * @ORM\JoinColumn(name="depo_id", nullable=true)
     */
    private $depo;

    /**
     * @var array $type
     *
     * @ORM\Column(name="payment_mode", type="string", length=255, columnDefinition="ENUM('FP', 'PP', 'HO', 'OP', 'DP')", nullable=true)
     */
    private $paymentMode;

    /**
     * @var boolean
     *
     * @ORM\Column(name="clearance_status", type="boolean", nullable=true)
     */
    private $clearanceStatus = false;

    /**
     * @var string
     *
     * @ORM\Column(name="clearance_remark", type="text", nullable=true)
     */
    private $clearanceRemark;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Rbs\Bundle\SalesBundle\Entity\DeliveryItem", mappedBy="order"))
     */
    private $deliveryItems;
    
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
        $this->deliveryItems = new ArrayCollection();
        $this->payments = new ArrayCollection();
    }

    /**
     * @param \Rbs\Bundle\SalesBundle\Entity\Payment $payment
     */
    public function addPayment($payment)
    {
        if($payment){
            if (!$this->payments->contains($payment)) {
                $payment->setAgent($this->getAgent());
                $this->payments->add($payment);
            }
        }
        return $this;
    }

    /**
     * @param \Rbs\Bundle\SalesBundle\Entity\Payment $payment
     */
    public function removePayment($payment)
    {
        $this->payments->removeElement($payment);
        $payment->removeOrder($this);

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
        $this->orderItems->removeElement($item);
        $item->setOrder(null);
    }

    /**
     * @return ArrayCollection
     */
    public function getOrderItems()
    {
        return $this->orderItems;
    }

    /**
     * @return string
     */
    public function getDeliveryState()
    {
        return $this->deliveryState;
    }

    /**
     * @param string $deliveryState
     */
    public function setDeliveryState($deliveryState)
    {
        $this->deliveryState = $deliveryState;
    }

    /**
     * @return string
     */
    public function getVehicleState()
    {
        return $this->vehicleState;
    }

    /**
     * @param string $vehicleState
     */
    public function setVehicleState($vehicleState)
    {
        $this->vehicleState = $vehicleState;
    }

    /**
     * @return string
     */
    public function getPaymentState()
    {
        return $this->paymentState;
    }

    /**
     * @param string $paymentState
     */
    public function setPaymentState($paymentState)
    {
        $this->paymentState = $paymentState;
    }

    /**
     * @return string
     */
    public function getOrderState()
    {
        return $this->orderState;
    }

    /**
     * @param string $orderState
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
    public function getTotalApprovedAmount()
    {
        return $this->totalApprovedAmount;
    }

    /**
     * @param float $totalApprovedAmount
     */
    public function setTotalApprovedAmount($totalApprovedAmount)
    {
        $this->totalApprovedAmount = $totalApprovedAmount;
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
     * @return string
     */
    public function getErrorMessage()
    {
        return $this->errorMessage;
    }

    /**
     * @param string $errorMessage
     */
    public function setErrorMessage($errorMessage)
    {
        $this->errorMessage = $errorMessage;
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

    /** @return float */
    public function getOrderItemsTotalQuantity()
    {
        $total = 0;
        /** @var OrderItem $item */
        foreach($this->orderItems as $item) {
            $total += $item->getQuantity();
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

    public function checkDeliveryState()
    {
        if($this->getDeliveryState() == Order::DELIVERY_STATE_READY 
            or $this->getDeliveryState() == Order::DELIVERY_STATE_PARTIALLY_SHIPPED
            or $this->getDeliveryState() == Order::ORDER_STATE_CANCEL){
            return true;
        }

        return false;
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

    public function getOrderIdAndDueAmount()
    {
        return '#' . ' '. $this->getId() . ' Due amount :' . ($this->getTotalAmount() - $this->getPaidAmount());
    }

    public function getOrderIdAndAgent()
    {
        return '#' . ' '. $this->getId() . ' Agent :' . $this->getAgent()->getUser()->getUsername();
    }

    public function getOrderInfo()
    {
        $date = $this->getCreatedAt()->setTimezone(new \DateTimeZone( 'Asia/Dhaka' ));
        return '#' . ' Order Id :'. $this->getId() . ', Amount :' . $this->getPaidAmount() .', Date:'. $date->format('Y-F-d, h:i A');
    }

    public function getOrderInfoForDamageGoods()
    {
        $date = $this->getCreatedAt()->setTimezone(new \DateTimeZone( 'Asia/Dhaka' ));
        return '#' . ' Order Id :'. $this->getId(). ' Depo :'. $this->getDepo()->getName(). ', Agent :' .
        ($this->getAgent()->getUser()->getProfile()->getFullName() ? $this->getAgent()->getUser()->getProfile()->getFullName()
            : $this->getAgent()->getUser()->getUsername()). ', Date : '. $date->format('Y-F-d, h:i A');
    }

    public function getOrderInfoWithAgent()
    {
        $date = $this->getCreatedAt()->setTimezone(new \DateTimeZone( 'Asia/Dhaka' ));
        return '#' . ' Order Id :'. $this->getId(). ', Agent :' .
            ($this->getAgent()->getUser()->getProfile()->getFullName() ? $this->getAgent()->getUser()->getProfile()->getFullName()
            : $this->getAgent()->getUser()->getUsername()). ', Date : '. $date->format('Y-F-d, h:i A');
    }

    public function getOrderInfoWithAgentForChick()
    {
        $date = $this->getCreatedAt()->setTimezone(new \DateTimeZone( 'Asia/Dhaka' ));
        return 'Order: '. $this->getId(). ', Agent: ' .
            ($this->getAgent()->getUser()->getProfile()->getFullName() ? $this->getAgent()->getUser()->getProfile()->getFullName()
            : $this->getAgent()->getUser()->getUsername()). ', Date: '. $date->format('d-m-Y');
    }

    public function getDueAmount()
    {
        return ($this->getTotalAmount() - $this->getPaidAmount());
    }

    /**
     * @return mixed
     */
    public function getDeliveries()
    {
        return $this->deliveries;
    }

    /**
     * @param mixed $deliveries
     */
    public function setDeliveries($deliveries)
    {
        $this->deliveries = $deliveries;
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

    public function categorySum()
    {
        $data = array();
        /** @var OrderItem $orderItem */
        foreach ($this->orderItems as $orderItem) {
            $categoryId = $orderItem->getItem()->getFirstCategory()->getId();
            
            if (!isset($data[$categoryId])) {
                $data[$categoryId] = 0;
            }

            $data[$categoryId] += $orderItem->getTotalAmount();
        }
        
        return $data;
    }

    /**
     * @return OrderIncentiveFlag
     */
    public function getOrderIncentiveFlag()
    {
        return $this->orderIncentiveFlag;
    }

    /**
     * @param mixed $orderIncentiveFlag
     */
    public function setOrderIncentiveFlag($orderIncentiveFlag)
    {
        $this->orderIncentiveFlag = $orderIncentiveFlag;
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
    public function getPaymentMode()
    {
        return $this->paymentMode;
    }

    /**
     * @param array $paymentMode
     */
    public function setPaymentMode($paymentMode)
    {
        $this->paymentMode = $paymentMode;
    }

    /**
     * @return bool
     */
    public function isClearanceStatus()
    {
        return $this->clearanceStatus;
    }

    /**
     * @param bool $clearanceStatus
     */
    public function setClearanceStatus($clearanceStatus)
    {
        $this->clearanceStatus = $clearanceStatus;
    }

    /**
     * @return string
     */
    public function getClearanceRemark()
    {
        return $this->clearanceRemark;
    }

    /**
     * @param string $clearanceRemark
     */
    public function setClearanceRemark($clearanceRemark)
    {
        $this->clearanceRemark = $clearanceRemark;
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
        /** @var OrderItem $orderItems */
        foreach ($this->getOrderItems() as $orderItems) {
            $orderItems->setOrder($this);
            $orderItems->calculateTotalAmount(true);
        }
        $this->setTotalAmount($this->getItemsTotalAmount());
    }

    public function getTotalPaymentDepositedAmount()
    {
        $data = 0;
        /** @var Payment $payment */
        foreach ($this->getPayments() as $payment) {
            if($payment->getTransactionType()==Payment::CR && $payment->getPaymentVia()!='TRANSPORT_COMMISSION'){
                $data+= $payment->getDepositedAmount();
            }
        }
        return $data;
    }

    public function calculateDueAmount(){
        return $this->getTotalAmount()-$this->getTotalPaymentActualAmount()-$this->getTotalPoTransportIncentive();
    }

    public function getTotalPaymentActualAmount()
    {
        $data = 0;
        /** @var Payment $payment */
        foreach ($this->getPayments() as $payment) {
            if($payment->getTransactionType()==Payment::CR && $payment->isVerified() && $payment->getPaymentVia()!='TRANSPORT_COMMISSION') {
                $data += $payment->getAmount();
            }
        }
        return $data;
    }


    /** @return float */
    public function getTotalDeliveryItemsQuantity()
    {
        $total = 0;
        /** @var Delivery $item */
        foreach($this->getDeliveries() as $item) {
            $total += $item->getTotalDeliveryItemsQuantity();
        }

        return $total;
    }

    /** @return float */
    public function getTotalDeliveryItemsAmount()
    {
        $total = 0;
        /** @var Delivery $item */
        foreach($this->getDeliveries() as $item) {
            /** @var DeliveryItem $deliveryItem */
            foreach ($item->getDeliveryItems() as $deliveryItem){

                $total += $deliveryItem->getOrderItem()->getPrice() * $deliveryItem->getQty();
            }

        }

        return $total;
    }

    public function getPaymentModeTitle(){
        $mode = $this->getPaymentMode();
        switch ($mode){
            case "FP":
                return 'Full Payment';
             case "PP":
                return 'Partial Payment';
             case "HO":
                return 'Head Office';
             case "OP":
                return 'Only Payment';
             case "DP":
                return 'Depot Payment';

        }
    }

    /**
     * @return ArrayCollection
     */
    public function getDeliveryItems()
    {
        return $this->deliveryItems;
    }

    /**
     * @param ArrayCollection $deliveryItems
     */
    public function setDeliveryItems($deliveryItems)
    {
        $this->deliveryItems = $deliveryItems;
    }

    public function getTotalTransportIncentive(){
        $total = 0;
        if($this->deliveryItems){
            /** @var DeliveryItem $item */
            foreach($this->deliveryItems as $item) {
                $total += $item->getTransportIncentiveAmount();
            }
        }
        return $total;
    }

    public function getTotalPoTransportIncentive(){
        $total = 0;
        if($this->orderItems){
            /** @var OrderItem $item */
            foreach($this->orderItems as $item) {
                $total += $item->getPoTransportIncentiveAmount();
            }
        }
        return $total;
    }
}