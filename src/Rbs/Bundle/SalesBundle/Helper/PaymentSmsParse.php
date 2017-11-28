<?php

namespace Rbs\Bundle\SalesBundle\Helper;

use Doctrine\ORM\EntityManager;
use Rbs\Bundle\SalesBundle\Entity\Sms;
use Rbs\Bundle\SalesBundle\Entity\Agent;
use Rbs\Bundle\SalesBundle\Entity\Payment;
use Symfony\Component\DependencyInjection\ContainerInterface;

class PaymentSmsParse
{
    /** @var  EntityManager */
    protected $em;

    /** @var  Sms */
    protected $sms;

    public $error;

    public $paymentType;

    /** @var Agent */
    protected $agent;

    /** @var Payment */
    protected $payment;
    protected $container;
    protected $mobileNumber;

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
        $this->payment = null;
        $this->error;
        $this->paymentType = null;

        $msg = $this->sms->getMsg();
        $splitMsg = array_filter(explode(',', $msg));

        $agentId = isset($splitMsg[0]) ? trim($splitMsg[0]) : 0;
        $bankAccountCode = isset($splitMsg[1]) ? trim($splitMsg[1]) : '';
        $this->paymentType = isset($splitMsg[2]) ? trim($splitMsg[2]) : '';

        if(empty($this->paymentType)){
            $this->setError('Invalid Payment Type');
            return false;
        }

        $this->setAgent($agentId);
        $this->setPayment($bankAccountCode, $agentId);

        $this->sms->setPaymentMode($this->paymentType);
        $this->em->persist($this->sms);
        $this->em->flush();

        return $this->sms->getId();
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

    protected function setPayment($accountInfo, $agentId)
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
                    $nourishBankCode = $this->em->getRepository('RbsSalesBundle:AgentNourishBank')->findOneBy(array('account' => $nourishBankAccount));
                    $agentBankAccount = $this->em->getRepository('RbsSalesBundle:AgentBank')->findOneBy(array('code' => $agentBank, 'agent' => $agent));

                    if (empty($fxCx)) {

                        $this->setError('Invalid Payment For');
                        break;
                    } else if (!$nourishBankCode) {
                        $this->setError('Invalid Nourish Bank Code');
                        $this->markError($nourishBankCode);
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
                            $this->payment->setFxCx($fxCx);
                            $this->payment->setAgentBankBranch($agentBankAccount);

                            $this->payment->setAgent($this->agent);
                            $this->payment->setPaymentMode($this->paymentType);
                            $this->payment->setTransactionType(Payment::CR);
                            $this->payment->setVerified(false);

                            $this->em->persist($this->payment);
                            $this->em->flush();
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

    public function smsService()
    {
        $smsSender = $this->container->get('rbs_erp.sales.service.smssender');
        $smsSender->agentBankInfoSmsAction("Your SMS text is unreadable.", $this->mobileNumber);
    }
}