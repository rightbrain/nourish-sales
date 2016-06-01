<?php

namespace Rbs\Bundle\SalesBundle\Datatables;

/**
 * Class CashDepositDatatable
 *
 * @package Rbs\Bundle\SalesBundle\Datatables
 */
class CashDepositDatatable extends BaseDatatable
{
    /**
     * {@inheritdoc}
     */
    public function buildDatatable()
    {
        $this->features->setFeatures($this->defaultFeatures());
        $this->options->setOptions($this->defaultOptions());

        $this->ajax->setOptions(array(
            'url' => $this->router->generate('cash_deposit_list_ajax'),
            'type' => 'GET'
        ));

        $this->columnBuilder
            ->add('depositedAt', 'datetime', array('title' => 'Date', 'date_format' => 'LLL' ))
            ->add('depo.name', 'column', array('title' => 'Depo'))
            ->add('depositedBy.username', 'column', array('title' => 'Depositor'))
            ->add('deposit', 'column', array('title' => 'Amount'))
            ->add('bankName', 'column', array('title' => 'Bank Name'))
            ->add('branchName', 'column', array('title' => 'Branch Name'))
            ->add('remark', 'column', array('title' => 'Remark'))
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getEntity()
    {
        return 'Rbs\Bundle\SalesBundle\Entity\CashDeposit';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'cash_deposit_datatable';
    }
}
