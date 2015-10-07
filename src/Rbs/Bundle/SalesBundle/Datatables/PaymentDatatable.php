<?php

namespace Rbs\Bundle\SalesBundle\Datatables;

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
    protected $allowCustomerSearch;

    public function getLineFormatter()
    {
        /** @var Profile $profile */
        $formatter = function($line){
            $line['bankInfo'] = 'Payment Method:'.$line['paymentMethod'].', Bank Name:'.$line['bankName'].', Branch Name:'.$line['branchName'];
            $line['totalAmount'] = '<div style="text-align: right;">'. number_format($line['amount'], 2) .'</div>';
            if ($this->allowCustomerSearch) {
                $profile = $this->em->getRepository('RbsUserBundle:Profile')->findOneBy(array('user' => $line['customer']['user']['id']));
                $line["fullName"] = $profile->getFullName();
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
        $this->allowCustomerSearch = $this->user->getUserType() != User::CUSTOMER;

        $this->features->setFeatures($this->defaultFeatures());
        $this->options->setOptions(array_merge($this->defaultOptions(), array(
            'individual_filtering' => true,
            'individual_filtering_position' => 'head',
            'order' => [[0, 'desc']],
        )));

        $this->callbacks->setCallbacks(array
            (
                'init_complete' => "function( settings ) {
                        Payment.filterInit({$this->allowCustomerSearch});
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
        if ($this->allowCustomerSearch) {
            $this->columnBuilder->add('customer.user.id', 'column', array('title' => 'Customer Name', 'render' => 'resolveCustomerName'));
        }

        $this->columnBuilder->add('amount', 'column', array('visible' => false))
            ->add('totalAmount', 'virtual', array('title' => 'Amount'))
            ->add('paymentMethod', 'column', array('visible' => false))
            ->add('bankName', 'column', array('visible' => false))
            ->add('branchName', 'column', array('visible' => false))
            ->add('bankInfo', 'virtual', array('title' => 'Bank Info'))
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
