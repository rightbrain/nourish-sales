<?php

namespace Rbs\Bundle\SalesBundle\Helper;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Rbs\Bundle\SalesBundle\Entity\Customer;
use Rbs\Bundle\SalesBundle\Entity\Delivery;
use Rbs\Bundle\SalesBundle\Entity\Order;
use Rbs\Bundle\SalesBundle\Entity\OrderItem;
use Rbs\Bundle\SalesBundle\Entity\Payment;
use Rbs\Bundle\SalesBundle\Entity\Sms;

class SmsParse
{
    /** @var  EntityManager */
    protected $em;

    /** @var  Sms */
    protected $sms;

    public $error;

    /** @var Customer */
    protected $customer;

    /** @var Order */
    protected $order ;

    /** @var Payment */
    protected $payment;

    protected $orderItems = array();

    public function __construct($em)
    {
        $this->em = $em;
    }

    protected function setError($string)
    {
        $this->error = $string;
    }

    protected function hasError()
    {
        return !empty($this->error);
    }

    public function parse(Sms $sms)
    {
        $this->sms = $sms;
        $this->order = null;
        $this->orderItems = array();
        $this->payment = null;
        $this->error;
        $this->validate();
        return $this->createOrder();
    }

    protected function validate()
    {
        $msg = $this->sms->getMsg();
        $splitMsg = array_filter(explode(',', $msg));

        $customerId = isset($splitMsg[0]) ? trim($splitMsg[0]) : 0;
        $orderInfo = isset($splitMsg[1]) ? trim($splitMsg[1]) : '';
        $bankName = isset($splitMsg[2]) ? trim($splitMsg[2]) : '';
        $bankBranch = isset($splitMsg[3]) ? trim($splitMsg[3]) : '';
        $amount = isset($splitMsg[4]) ? trim($splitMsg[4]) : '';

        $this->setCustomer($customerId);
        $this->setOrderItems($orderInfo);
        $this->setPayment($bankName, $bankBranch, $amount);
    }

    public function createOrder()
    {
        if ($this->hasError()) {

            $this->sms->setStatus('UNREAD');
            $this->sms->setRemark($this->error);
            $this->em->persist($this->sms);
            $this->em->flush();

            return false;
        }

        $this->order = new Order();

        $this->sms->setStatus('READ');
        $this->sms->setCustomer($this->customer);
        $this->sms->setOrder($this->order);

        /** @var OrderItem $orderItem */
        foreach ($this->orderItems as $orderItem) {
            $this->order->addOrderItem($orderItem);
            $orderItem->setOrder($this->order);
        }

        $this->order->setCustomer($this->customer);
        $this->order->setTotalAmount($this->order->getItemsTotalAmount());
        $this->order->setOrderState(Order::ORDER_STATE_PENDING);
        $this->order->setPaymentState(Order::PAYMENT_STATE_PENDING);
        $this->order->setDeliveryState(Order::DELIVERY_STATE_PENDING);
        $this->order->setOrderVia('SMS');
        $this->order->setRefSMS($this->sms);

        if ($this->payment) {
            $payments = new ArrayCollection();
            $payments->add($this->payment);
            $this->order->setPayments($payments);
            $this->payment->addOrder($this->order);
            $this->payment->setCustomer($this->customer);
            $this->em->getRepository('RbsSalesBundle:Order')->orderAmountAdjust($this->payment);
        }

        $delivery = new Delivery();
        $delivery->setOrderRef($this->order);
        $delivery->setWarehouse($this->customer->getWarehouse());

        $this->em->persist($this->payment);
        $this->em->persist($this->order);
        $this->em->persist($delivery);
        $this->em->flush();
        $this->em->clear();

        return array(
            'orderId' => $this->order->getId()
        );
    }

    public function markError($string)
    {
        $this->sms->setMsg(str_replace($string, '<span class="error">'.$string.'</span>', $this->sms->getMsg()));
    }

    protected function setCustomer($customerId)
    {
        $this->customer = $this->em->getRepository('RbsSalesBundle:Customer')->findOneBy(array('customerID' => $customerId));

        if (!$this->customer) {
            $this->setError('Invalid customer ID');
            $this->markError($customerId);
        } else {

            $userMobile = $this->trimMobileNo($this->customer->getUser()->getProfile()->getCellphone());
            $smsMobileNo = $this->trimMobileNo($this->sms->getMobileNo());

            if (!$this->endsWith($userMobile, $smsMobileNo)) {
                $this->setError('Customer mobile no does not match with mobile number of sms');
            }
        }

        return $this->customer;
    }

    protected function setOrderItems($orderInfo)
    {
        if ($this->hasError()) {
            return;
        }

        $itemRepo = $this->em->getRepository('RbsCoreBundle:Item');

        try {
            $orderItems = explode('-', $orderInfo);

            if (empty($orderItems)) {
                $this->setError('Invalid order information');
                $this->markError($orderInfo);
            }

            foreach ($orderItems as $orderItem) {
                list($sku, $qty) = explode(':', $orderItem);
                $item = $itemRepo->findOneBy(array('sku' => trim($sku)));

                if (!$item) {
                    $this->setError('Invalid produce code');
                    $this->markError($sku);
                    break;
                } else if (!preg_match('/^\d+$/', trim($qty))) {
                    $this->setError('Invalid qty code');
                    $this->markError($qty);
                    break;
                } else {
                    $orderItem = new OrderItem();
                    $orderItem->setItem($item);
                    $orderItem->setQuantity((int)$qty);
                    $orderItem->setPrice($item->getPrice());
                    $orderItem->calculateTotalAmount(true);
                    $this->orderItems[] = $orderItem;
                }
            }

        } catch (\Exception $e) {
            $this->setError("Invalid product:qty format");
        }
    }

    protected function setPayment($bankName = '', $bankBranch = '', $amount = '')
    {
        if ($this->hasError()) {
            return;
        }

        if (!empty($amount) && !preg_match('/^\d+$/', trim($amount))) {
            $this->setError('Invalid amount');
            $this->markError($amount);
            return;
        }

        if (!empty($amount) && (empty(trim($bankName)) || empty(trim($bankBranch)))) {
            $this->setError('Invalid bank or branch name');
            return;
        }

        if (!empty($amount)) {
            $this->payment = new Payment();
            $this->payment->setAmount($amount);
            $this->payment->setBankName($bankName);
            $this->payment->setBranchName($bankBranch);
            $this->payment->setDepositDate(new \DateTime());
        }
    }

    function startsWith($haystack, $needle) {
        // search backwards starting from haystack length characters from the end
        return $needle === "" || strrpos($haystack, $needle, -strlen($haystack)) !== FALSE;
    }

    function endsWith($haystack, $needle) {
        // search forward starting from end minus needle length characters
        return $needle === "" || (($temp = strlen($haystack) - strlen($needle)) >= 0 && strpos($haystack, $needle, $temp) !== FALSE);
    }

    public function trimMobileNo($string)
    {
        return str_replace(array(' ', '+'), '', $string);
    }
}