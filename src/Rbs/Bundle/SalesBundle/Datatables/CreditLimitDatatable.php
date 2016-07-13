<?php

namespace Rbs\Bundle\SalesBundle\Datatables;

use Rbs\Bundle\SalesBundle\Entity\CreditLimit;

/**
 * Class CreditLimitDatatable
 *
 * @package Rbs\Bundle\SalesBundle\Datatables
 */
class CreditLimitDatatable extends BaseDatatable
{
    /**
     * {@inheritdoc}
     */
    public function buildDatatable()
    {
        $this->features->setFeatures($this->defaultFeatures());
        $this->options->setOptions($this->defaultOptions());

        $this->ajax->setOptions(array(
            'url' => $this->router->generate('credit_limit_list_ajax'),
            'type' => 'GET'
        ));

        $this->columnBuilder
            ->add('agent.user.username', 'column', array('title' => 'Agent'))
            ->add('startDate', 'datetime', array('title' => 'Start Date', 'date_format' => 'LL' ))
            ->add('endDate', 'datetime', array('title' => 'End Date', 'date_format' => 'LL' ))
            ->add('status', 'column', array('title' => 'Status'))
            ->add('amount', 'column', array('title' => 'amount'))
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getEntity()
    {
        return 'Rbs\Bundle\SalesBundle\Entity\CreditLimit';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'credit_limit_datatable';
    }
}
