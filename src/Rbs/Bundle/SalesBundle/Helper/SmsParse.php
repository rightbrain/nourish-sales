<?php

namespace Rbs\Bundle\SalesBundle\Helper;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Rbs\Bundle\SalesBundle\Entity\Agent;
use Rbs\Bundle\SalesBundle\Entity\Delivery;
use Rbs\Bundle\SalesBundle\Entity\Order;
use Rbs\Bundle\SalesBundle\Entity\OrderIncentiveFlag;
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

    /** @var Agent */
    protected $agent;

    /** @var Order */
    protected $order ;

    /** @var Order */
    protected $orderIncentiveFlag;

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
        $this->orderIncentiveFlag = null;
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

        $agentId = isset($splitMsg[0]) ? trim($splitMsg[0]) : 0;
        $orderInfo = isset($splitMsg[1]) ? trim($splitMsg[1]) : '';
        $bankAccountCode = isset($splitMsg[2]) ? trim($splitMsg[2]) : '';

        $this->setAgent($agentId);
        $this->setOrderItems($orderInfo);
        $this->setPayment($bankAccountCode, $agentId);
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
        $this->orderIncentiveFlag = new OrderIncentiveFlag();

        $this->sms->setStatus('READ');
        $this->sms->setAgent($this->agent);
        $this->sms->setOrder($this->order);
        $this->order->setLocation($this->agent->getUser()->getUpozilla());

        /** @var OrderItem $orderItem */
        foreach ($this->orderItems as $orderItem) {
            $this->order->addOrderItem($orderItem);
            $orderItem->setOrder($this->order);
        }

        $this->order->setAgent($this->agent);
        $this->order->setDepo($this->agent->getDepo());
        $this->order->setTotalAmount($this->order->getItemsTotalAmount());
        $this->order->setOrderState(Order::ORDER_STATE_PENDING);
        $this->order->setPaymentState(Order::PAYMENT_STATE_PENDING);
        $this->order->setDeliveryState(Order::DELIVERY_STATE_PENDING);
        $this->order->setOrderVia('SMS');
        $this->order->setRefSMS($this->sms);
        $this->em->persist($this->order);

        $this->orderIncentiveFlag->setOrder($this->order);
        $this->em->persist($this->orderIncentiveFlag);


        ##### order wise payment business logic removed #####
        ##### but previous relation still on table #####

//        if ($this->payment) {
//            $this->payment->addOrder($this->order);
//            $this->payment->setAgent($this->agent);
//            $this->payment->setTransactionType(Payment::CR);
//            $this->payment->setVerified(false);
//            $this->em->persist($this->payment);
//

//            $payments = new ArrayCollection();
//            $this->order->setPayments($payments);
//            $payments->add($this->payment);
//        }
        $this->em->flush();

        return array(
            'orderId' => $this->order->getId()
        );
    }

    public function markError($string)
    {
        $this->sms->setMsg(str_replace($string, '<span class="error">'.$string.'</span>', $this->sms->getMsg()));
    }

    protected function setAgent($agentId)
    {
        $this->agent = $this->em->getRepository('RbsSalesBundle:Agent')->findOneBy(array('agentID' => $agentId));

        if (!$this->agent) {
            $this->setError('Invalid Agent ID');
            $this->markError($agentId);
        } else {

            $userMobile = $this->trimMobileNo($this->agent->getUser()->getProfile()->getCellphone());
            $smsMobileNo = $this->trimMobileNo($this->sms->getMobileNo());

            if (!$this->endsWith($userMobile, $smsMobileNo)) {
                $this->setError('Agent mobile no does not match with mobile number of sms');
            }
        }

        return $this->agent;
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
                $this->setError('Invalid Order Information');
                $this->markError($orderInfo);
            }

            foreach ($orderItems as $orderItem) {
                list($sku, $qty) = explode(':', $orderItem);
                $item = $itemRepo->findOneBy(array('sku' => trim($sku)));

                if (!$item) {
                    $this->setError('Invalid Produce Code');
                    $this->markError($sku);
                    break;
                } else if (!preg_match('/^\d+$/', trim($qty))) {
                    $this->setError('Invalid Quantity');
                    $this->markError($qty);
                    break;
                } else if ($this->agent->getItemType() != null and  $this->agent->getItemType() != $item->getItemType()) {
                    $this->setError('Product Type Not Match');
                    $this->markError($sku);
                    break;
                } else {
                    $orderItem = new OrderItem();
                    $orderItem->setItem($item);
                    $orderItem->setQuantity((int)$qty);

                    $price = $this->em->getRepository('RbsCoreBundle:ItemPrice')->getCurrentPrice($item, $this->agent->getUser()->getZilla());
                    $orderItem->setPrice($price);
                    $orderItem->calculateTotalAmount(true);
                    $this->orderItems[] = $orderItem;
                }
            }

        } catch (\Exception $e) {
            $this->setError("Invalid Product:Quantity Format");
        }
    }

    protected function setPayment($accountInfo, $agentId)
    {
        if ($this->hasError()) {
            return;
        }
        $agent = $this->em->getRepository('RbsSalesBundle:Agent')->findOneBy(array('agentID' => $agentId));
        try {
            $accounts = explode('-', $accountInfo);
            foreach ($accounts as $account) {

                list($agentBank, $nourishBank, $amount) = explode(':', $account);

                $nourishBankAccount = $this->em->getRepository('RbsCoreBundle:BankAccount')->findOneBy(array('code' => $nourishBank));
                $agentBankAccount = $this->em->getRepository('RbsSalesBundle:AgentBank')->findOneBy(array('code' => $agentBank, 'agent' => $agent));

                if (!$nourishBankAccount) {
                    $this->setError('Invalid Nourish Bank Code');
                    $this->markError($nourishBankAccount);
                    break;
                } else if (!$agentBankAccount) {
                    $this->setError('Invalid Agent Bank Code');
                    $this->markError($agentBankAccount);
                    break;
                } else if (!empty($amount) && !preg_match('/^\d+$/', trim($amount))) {
                    $this->setError('Invalid Amount');
                    $this->markError($amount);
                    break;
                } else {
                    if (!empty($amount)) {
                        $this->payment = new Payment();
                        $this->payment->setAmount(0);
                        $this->payment->setDepositedAmount($amount);
                        $this->payment->setBankAccount($nourishBankAccount);
                        $this->payment->setVerified(false);
                        $this->payment->setDepositDate(date("Y-m-d"));
                        $this->payment->setPaymentVia('SMS');
                        $this->payment->setAgentBankBranch($agentBankAccount);

                        $this->payment->setAgent($this->agent);
                        $this->payment->setTransactionType(Payment::CR);
                        $this->payment->setVerified(false);

                        $this->em->persist($this->payment);
                    }
                }
            }
        } catch (\Exception $e) {
            $this->setError("Invalid Bank, Code and Amount Format");
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