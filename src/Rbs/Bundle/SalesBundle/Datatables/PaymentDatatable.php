<?php

namespace Rbs\Bundle\SalesBundle\Datatables;

use Rbs\Bundle\SalesBundle\Entity\Agent;
use Rbs\Bundle\SalesBundle\Entity\Payment;
use Rbs\Bundle\UserBundle\Entity\Profile;
use Rbs\Bundle\UserBundle\Entity\User;

/**
 * Class PaymentDatatable
 *
 * @package Rbs\Bundle\SalesBundle\Datatables
 */
class PaymentDatatable extends BaseDatatable
{
    private $user;
    protected $allowAgentSearch;

    public function getLineFormatter()
    {
        /** @var Profile $profile
        /** @var Payment $payment
         * @return mixed
         */
        $formatter = function($line){
            $payment = $this->em->getRepository('RbsSalesBundle:Payment')->find($line['id']);
            //$line['bankInfo'] = 'Payment Method:'.$payment->getPaymentMethod().', Bank Name:'.$payment->getBankName().', Branch Name:'.$payment->getBranchName();
            $line['totalAmount'] = '<div style="text-align: right;">'. number_format($payment->getAmount(), 2) .'</div>';
            $line['isVerifiedTrue'] = $payment->isVerifiedTrue();
            if ($this->allowAgentSearch) {
                $profile = $this->em->getRepository('RbsUserBundle:Profile')->findOneBy(array('user' => $payment->getAgent()->getUser()->getId()));
                $line["fullName"] = Agent::agentIdNameFormat($payment->getAgent()->getAgentID(), $profile->getFullName());
            }

            return $line;
        };

        return $formatter;
    }

    /**
     * {@inheritdoc}
     */
    public function buildDatatable()
    {
        /** @var User $user */
        $this->user = $this->securityToken->getToken()->getUser();
        $this->allowAgentSearch = $this->user->getUserType() != User::AGENT;

        $this->features->setFeatures($this->defaultFeatures());
        $this->options->setOptions(array_merge($this->defaultOptions(), array(
            'individual_filtering' => true,
            'individual_filtering_position' => 'head',
            'order' => [[0, 'desc']],
        )));

        $this->callbacks->setCallbacks(array
            (
                'init_complete' => "function( settings ) {
                        Payment.filterInit({$this->allowAgentSearch});
                }"
            )
        );

        $this->ajax->setOptions(array(
            'url' => $this->router->generate('payment_list_ajax'),
            'type' => 'GET'
        ));

        $twigVars = $this->twig->getGlobals();
        $dateFormat = isset($twigVars['js_moment_date_format']) ? $twigVars['js_moment_date_format'] : 'D-MM-YY';

        $this->columnBuilder->add('createdAt', 'datetime', array( 'title' => 'Date', 'date_format' => $dateFormat ));
        if ($this->allowAgentSearch) {
            $this->columnBuilder->add('agent.user.id', 'column', array('title' => 'Agent Name', 'render' => 'resolveAgentName'));
        }

        $this->columnBuilder->add('amount', 'column', array('visible' => false))
            ->add('totalAmount', 'virtual', array('title' => 'Amount'))
            ->add('paymentMethod', 'column', array('visible' => false))
            ->add('bankAccount.name', 'column', array('title' => 'Account'))
            //->add('branchName', 'column', array('visible' => false))
            //->add('bankInfo', 'virtual', array('title' => 'Bank Info'))
            ->add('isVerifiedTrue', 'virtual', array('title' => 'Verified', 'visible' => true))
            ->add('orders.id', 'array', array(
                'title' => 'Orders',
                'data' => 'orders[, ].id'
            ))
            ->add('remark', 'column', array('title' => 'Remarks'))
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getEntity()
    {
        return 'Rbs\Bundle\SalesBundle\Entity\Payment';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'payment_datatable';
    }
}
