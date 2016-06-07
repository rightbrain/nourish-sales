<?php

namespace Rbs\Bundle\SalesBundle\Datatables;

/**
 * Class AgentsBankInfoDatatable
 *
 * @package Rbs\Bundle\SalesBundle\Datatables
 */
class AgentsBankInfoDatatable extends BaseDatatable
{
    /**
     * {@inheritdoc}
     */
    public function buildDatatable()
    {
        $this->features->setFeatures($this->defaultFeatures());
        $this->options->setOptions($this->defaultOptions());

        $this->ajax->setOptions(array(
            'url' => $this->router->generate('bank_info_list_ajax'),
            'type' => 'GET'
        ));

        $twigVars = $this->twig->getGlobals();
        $dateFormat = isset($twigVars['js_moment_date_format']) ? $twigVars['js_moment_date_format'] : 'D-MM-YY';

        $this->columnBuilder
            ->add('createdAt', 'datetime', array('title' => 'Date', 'date_format' => $dateFormat))
            ->add('bankName', 'column', array('title' => 'Bank Name'))
            ->add('branchName', 'column', array('title' => 'Branch Name'))
            ->add('remark', 'column', array('title' => 'Remark'))
            ->add('amount', 'column', array('title' => 'Amount'))
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getEntity()
    {
        return 'Rbs\Bundle\SalesBundle\Entity\AgentsBankInfo';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'agents_bank_info_datatable';
    }
}
