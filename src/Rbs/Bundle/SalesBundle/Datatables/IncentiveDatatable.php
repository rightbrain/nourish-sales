<?php

namespace Rbs\Bundle\SalesBundle\Datatables;
use Rbs\Bundle\SalesBundle\Entity\Incentive;

/**
 * Class IncentiveDatatable
 *
 * @package Rbs\Bundle\SalesBundle\Datatables
 */
class IncentiveDatatable extends BaseDatatable
{
    public function getLineFormatter()
    {
        /** @var Incentive $order
         * @return mixed
         */
        $formatter = function($line){
            $incentive = $this->em->getRepository('RbsSalesBundle:Incentive')->find($line['id']);
            $line["isActive"] = $incentive->isActive();
            return $line;
        };

        return $formatter;
    }

    /**
     * {@inheritdoc}
     */
    public function buildDatatable()
    {
        $this->features->setFeatures($this->defaultFeatures());
        $this->options->setOptions($this->defaultOptions());

        $this->ajax->setOptions(array(
            'url' => $this->router->generate('incentives_list_ajax'),
            'type' => 'GET'
        ));

        $this->columnBuilder
            ->add('createdAt', 'datetime', array('title' => 'Created Date','date_format' => 'LLL' ))
            ->add('agent.user.username', 'column', array('title' => 'Agent'))
            ->add('amount', 'column', array('title' => 'Amount'))
            ->add('duration', 'column', array('title' => 'Duration'))
            ->add('type', 'column', array('title' => 'Type'))
            ->add('status', 'column', array('title' => 'Status'))
            ->add('isActive', 'virtual', array('visible' => false))
            ->add(null, 'action', array(
                'width' => '180px',
                'title' => 'Action',
                'actions' => array(
                    $this->makeActionButton('incentive_approve', array('id' => 'id'), 'ROLE_ADMIN', 'Approve', 'Approve', 'fa fa-pencil-square-o', '', array('render_if' => array('isActive'))),
                    $this->makeActionButton('incentive_cancel', array('id' => 'id'), 'ROLE_ADMIN', 'Cancel', 'Cancel', 'fa fa-minus', '', array('render_if' => array('isActive'))),
                    $this->makeActionButton('incentive_details', array('id' => 'id'), 'ROLE_ADMIN', 'View', 'View', 'fa fa-eye'),
                )
            ))
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getEntity()
    {
        return 'Rbs\Bundle\SalesBundle\Entity\Incentive';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'incentive_datatable';
    }
}
