<?php

namespace Rbs\Bundle\SalesBundle\Datatables;

/**
 * Class DamageGoodDatatable
 *
 * @package Rbs\Bundle\SalesBundle\Datatables
 */
class DamageGoodAdminDatatable extends BaseDatatable
{
    /**
     * {@inheritdoc}
     */
    public function buildDatatable()
    {
        $this->features->setFeatures($this->defaultFeatures());
        $this->options->setOptions($this->defaultOptions());

        $this->ajax->setOptions(array(
            'url' => $this->router->generate('damage_good_admin_list_ajax'),
            'type' => 'GET'
        ));

        $twigVars = $this->twig->getGlobals();
        $dateFormat = isset($twigVars['js_moment_date_format']) ? $twigVars['js_moment_date_format'] : 'D-MM-YY';

        $this->columnBuilder
            ->add('createdAt', 'datetime', array('title' => 'Date', 'date_format' => $dateFormat))
            ->add('user.username', 'column', array('title' => 'User'))
            ->add('agent.user.username', 'column', array('title' => 'Agent'))
            ->add('remark', 'column', array('title' => 'Remark'))
            ->add('amount', 'column', array('title' => 'Amount'))
            ->add('refundAmount', 'column', array('title' => 'Refund'))
            ->add('status', 'column', array('title' => 'Status'))
            ->add(null, 'action', array(
                'width' => '180px',
                'title' => 'Action',
                'actions' => array(
                    $this->makeActionButton('damage_goods_verify', array('id' => 'id'), 'ROLE_DAMAGE_GOODS_VERIFY', 'Verify', 'Verify', 'fa fa-pencil-square-o'),
                    $this->makeActionButton('damage_goods_view', array('id' => 'id'), 'ROLE_DAMAGE_GOODS_APPROVE', 'View', 'View', 'fa fa-eye'),
                )
            ))
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getEntity()
    {
        return 'Rbs\Bundle\SalesBundle\Entity\DamageGood';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'damage_good_datatable';
    }
}
