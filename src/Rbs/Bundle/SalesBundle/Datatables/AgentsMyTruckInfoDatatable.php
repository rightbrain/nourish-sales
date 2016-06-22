<?php

namespace Rbs\Bundle\SalesBundle\Datatables;

/**
 * Class AgentsTruckInfoDatatable
 *
 * @package Rbs\Bundle\SalesBundle\Datatables
 */
class AgentsMyTruckInfoDatatable extends BaseDatatable
{
    /**
     * {@inheritdoc}
     */
    public function buildDatatable()
    {
        $this->features->setFeatures($this->defaultFeatures());
        $this->options->setOptions($this->defaultOptions());

        $this->ajax->setOptions(array(
            'url' => $this->router->generate('truck_info_my_list_ajax'),
            'type' => 'GET'
        ));

        $twigVars = $this->twig->getGlobals();
        $dateFormat = isset($twigVars['js_moment_date_format']) ? $twigVars['js_moment_date_format'] : 'D-MM-YY';

        $this->columnBuilder
            ->add('createdAt', 'datetime', array('title' => 'Date', 'date_format' => $dateFormat))
            ->add('order.id', 'column', array('title' => 'Order Number'))
            ->add('driverName', 'column', array('title' => 'Driver Name'))
            ->add('driverPhone', 'column', array('title' => 'Driver Phone'))
            ->add('remark', 'column', array('title' => 'Remark'))
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getEntity()
    {
        return 'Rbs\Bundle\SalesBundle\Entity\AgentsTruckInfo';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'agents_truck_info_datatable';
    }
}
