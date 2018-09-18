<?php

namespace Rbs\Bundle\SalesBundle\Helper;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Rbs\Bundle\SalesBundle\Entity\Agent;
use Rbs\Bundle\SalesBundle\Entity\Order;
use Rbs\Bundle\SalesBundle\Entity\OrderIncentiveFlag;
use Rbs\Bundle\SalesBundle\Entity\OrderItem;
use Rbs\Bundle\SalesBundle\Entity\Payment;
use Rbs\Bundle\SalesBundle\Entity\Sms;
use Symfony\Component\DependencyInjection\ContainerInterface;

class SmsParse
{
    /** @var  EntityManager */
    protected $em;

    /** @var  Sms */
    protected $sms;

    public $error;

//    public $paymentType;

    /** @var Agent */
    protected $agent;

    /** @var Order */
    protected $order ;

    /** @var Order */
    protected $orderIncentiveFlag;

    /** @var Payment */
    protected $payment;
    protected $container;
    protected $mobileNumber;

    protected $orderItems = array();
    protected $payments = array();

    public $paymentMode;
    public $orderVia;

    public $agentSmsSegment;
    public $paymentInfoSmsSegment;

    public function __construct($em, ContainerInterface $c, $mobileNumber)
    {
        $this->em = $em;
        $this->container = $c;
        $this->mobileNumber = $mobileNumber;
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
        $this->payments = array();
        $this->payment = null;
        $this->error;
        $this->paymentMode = 'FP';
        $this->orderVia = 'SMS';
       return $this->validate();

    }

    protected function validate()
    {
        $msg = $this->sms->getMsg();
        $splitMsg = array_filter(explode(',', $msg));

        $agentId = isset($splitMsg[0]) ? trim($splitMsg[0]) : 0;
        $orderInfo = isset($splitMsg[1]) ? trim($splitMsg[1]) : '';
        $bankAccountCode = isset($splitMsg[2]) ? trim($splitMsg[2]) : '';
        $paymentMode = isset($splitMsg[3]) ? trim($splitMsg[3]) : 'FP';
        $orderVia = isset($splitMsg[4]) ? trim($splitMsg[4]) : 'SMS';

        $this->agentSmsSegment = $agentId;
        $this->paymentInfoSmsSegment = $bankAccountCode;

        $this->orderVia = $orderVia;

        if($this->orderVia=='APP' && $this->setAgent($agentId)==1){
            return array('message'=>'Invalid Agent ID','status'=>401);
        }elseif ($this->orderVia=='APP' && $this->setAgent($agentId)==2){
            return array('message'=>'Agent mobile no does not match with mobile number of sms','status'=>401);
        }else{
            $this->setAgent($agentId);
        }
        if($this->orderVia=='APP' && $this->setPaymentMode($paymentMode)==1){
            return array('message'=>'Invalid Payment Mode','status'=>404);
        }else{
            $this->setPaymentMode($paymentMode);
        }

        if (!empty($agentId) && !empty($orderInfo)){
            $this->setOrderItems($orderInfo);
           return $this->createOrder();
        }
        if(!empty($agentId) && empty($orderInfo) && !empty($bankAccountCode)){
          return $this->setPayment($this->paymentInfoSmsSegment, $this->agentSmsSegment, true);
        }
//        return false;
    }

    public function createOrder()
    {
        if ($this->hasError()) {
            if($this->orderVia!='APP'){
                $this->smsService();
            }
            $this->sms->setStatus('UNREAD');
            $this->sms->setRemark($this->error);
            $this->em->persist($this->sms);
            $this->em->flush();
            return array('message'=>$this->error, 'status'=>404);
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
        $this->order->setOrderVia($this->orderVia);
        $this->order->setRefSMS($this->sms);
        $this->order->setPaymentMode($this->paymentMode);
        $this->em->persist($this->order);

        $this->orderIncentiveFlag->setOrder($this->order);
        $this->em->persist($this->orderIncentiveFlag);

        $this->setPayment($this->paymentInfoSmsSegment, $this->agentSmsSegment, false);

        $payments = new ArrayCollection();
        if ($this->hasError()) {
            $this->order->setErrorMessage($this->error);
        }
            /** @var Payment $payment */
            foreach ($this->payments as $payment) {
                $payment->addOrder($this->order);
                $this->em->persist($payment);
                $this->order->setPayments($payments);
                $payments->add($payment);

            }

        $this->em->flush();
        if($this->orderVia!='APP') {
            $msg = "Dear Customer, Your Order No: " . $this->order->getId() . " / " . date('d-m-Y') . '. ';
            if ($this->order->getOrderItems()) {
                $msg .= 'Product Info: ';
                $i = 1;
                $array_count = count($this->order->getOrderItems());
                foreach ($this->order->getOrderItems() as $item) {
                    $msg .= $item->getItem()->getSku() . '-' . $item->getQuantity();
                    if ($i == $array_count) {
                        $msg .= '.';
                    } else {
                        $msg .= ', ';
                    }
                    $i++;
                }
            }
            $part1s = str_split($msg, $split_length = 160);
            foreach ($part1s as $part) {
                $smsSender = $this->container->get('rbs_erp.sales.service.smssender');
                $smsSender->agentBankInfoSmsAction($part, $this->order->getAgent()->getUser()->getProfile()->getCellphone());
            }
        }
//        $smsSender = $this->container->get('rbs_erp.sales.service.smssender');
//        $smsSender->agentBankInfoSmsAction("Your Order No:".$this->order->getId().".", $this->order->getAgent()->getUser()->getProfile()->getCellphone());

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
            return 1;
        } else {

            $userMobile = $this->trimMobileNo($this->agent->getUser()->getProfile()->getCellphone());
            $smsMobileNo = $this->trimMobileNo($this->sms->getMobileNo());

            if (!$this->endsWith($userMobile, $smsMobileNo)) {
                $this->setError('Agent mobile no does not match with mobile number of sms');
                return  2;
            }
        }

        return $this->agent->getId();
    }

    protected function setPaymentMode($paymentMode)
    {
        $allPaymentMode = array('FP','PP','HO','DP','OP');

        if (!in_array($paymentMode, $allPaymentMode)) {
            $this->setError('Invalid Payment Mode');
            $this->markError($paymentMode);
            return 1;
        }

        return $this->paymentMode = $paymentMode;
    }

    protected function setOrderItems($orderInfo)
    {
        if ($this->hasError()) {
            if($this->orderVia!='APP'){
                $this->smsService();
            }
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
                    if($this->orderVia!='APP'){
                        $this->smsService();
                    }
                    $this->setError('Invalid Product Code');
                    $this->markError($sku);
                    break;
                } else if (!preg_match('/^\d+$/', trim($qty))) {
                    if($this->orderVia!='APP'){
                        $this->smsService();
                    }
                    $this->setError('Invalid Quantity');
                    $this->markError($qty);
                    break;
                } else if ($this->agent->getItemType() != null and  $this->agent->getItemType() != $item->getItemType()) {
                    if($this->orderVia!='APP'){
                        $this->smsService();
                    }
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
            if($this->orderVia!='APP'){
                $this->smsService();
            }
            $this->setError("Invalid Product:Quantity Format");
            return 5;
        }
    }

    protected function setPayment($accountInfo, $agentId, $sendSms=false )
    {
        if ($this->hasError()) {
            return;
        }
        $agent = $this->em->getRepository('RbsSalesBundle:Agent')->findOneBy(array('agentID' => $agentId));

        try {
            $accounts = explode('-', $accountInfo);
            foreach ($accounts as $account) {

                list($fxCx, $agentBank, $nourishBank, $amount) = explode(':', $account);

                $nourishBankAccount = $this->em->getRepository('RbsCoreBundle:BankAccount')->findOneBy(array('code' => $nourishBank));
                $nourishBankCode = $this->em->getRepository('RbsSalesBundle:AgentNourishBank')->findOneBy(array('account' => $nourishBankAccount, 'agent'=>$agent));
                $agentBankAccount = $this->em->getRepository('RbsSalesBundle:AgentBank')->findOneBy(array('code' => $agentBank, 'agent' => $agent));

                    if (empty($fxCx)) {
                        $this->setError('Invalid Payment For');
                        break;
                    } else if ($fxCx != "FD") {
                        $this->setError('Invalid Parameter, Need FD');
                        break;
                    } else if (!$nourishBankCode) {
                        $this->setError('Nourish Bank Code is not assigned yet.');
//                        $this->markError($nourishBankCode);
                        break;
                    } else if ($nourishBankCode->getAccount()->getCode()!= $nourishBank) {
                        $this->setError('Invalid Nourish Bank Code.');
//                        $this->markError($nourishBankCode);
                        break;
                    } else if (!$agentBankAccount) {
                        $this->setError('Invalid Agent Bank Code');
//                        $this->markError($agentBankAccount);
                        break;
                    } else if (!empty($amount) && !preg_match('/^\d+$/', trim($amount))) {
                        $this->setError('Invalid Amount');
//                        $this->markError($amount);
                        break;
                    } else {
                        if (!empty($amount)) {
                            $this->payment = new Payment();
                            $this->payment->setAmount(0);
                            $this->payment->setDepositedAmount($amount);
                            $this->payment->setBankAccount($nourishBankAccount);
                            $this->payment->setVerified(false);
//                            $this->payment->setDepositDate(date("Y-m-d"));
                            $this->payment->setPaymentVia('SMS');
                            $this->payment->setFxCx($fxCx);
                            $this->payment->setAgentBankBranch($agentBankAccount);

                            $this->payment->setAgent($this->agent);
                            $this->payment->setTransactionType(Payment::CR);
                            $this->payment->setVerified(false);

                            $this->payments[] = $this->payment;
                            $this->em->persist($this->payment);
                        }
                    }
                }
            $this->em->flush();
            if($this->orderVia!='APP') {
                if ($sendSms) {

                    $msg = "Dear Customer, Payment Placed Successfully ";
                    if ($this->payments) {
                        $i = 1;
                        $total_amount = 0;
                        $array_count = count($this->payments);
                        foreach ($this->payments as $payment) {
                            $total_amount += $payment->getDepositedAmount();

                            $i++;
                        }
                        $msg .= 'Tk' . $total_amount . '.';
                    }
                    $part1s = str_split($msg, $split_length = 160);
                    foreach ($part1s as $part) {
                        $smsSender = $this->container->get('rbs_erp.sales.service.smssender');
                        $smsSender->agentBankInfoSmsAction($part, $this->payments[0]->getAgent()->getUser()->getProfile()->getCellphone());
                    }
                }
            }

            return array(
                'paymentSuccess' => 'Payment Placed Successfully',
            );
            }
        catch
            (\Exception $e) {
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

    public function smsService()
    {
        $smsSender = $this->container->get('rbs_erp.sales.service.smssender');
        $smsSender->agentBankInfoSmsAction("Your Order SMS text is unreadable.", $this->mobileNumber);
    }
}