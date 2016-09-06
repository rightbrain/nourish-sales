<?php

namespace Rbs\Bundle\SalesBundle\Datatables;

/**
 * Class TruckInfoDatatable
 *
 * @package Rbs\Bundle\SalesBundle\Datatables
 */
class TruckInfoDatatable extends BaseDatatable
{
    /**
     * {@inheritdoc}
     */
    public function buildDatatable()
    {
        $this->features->setFeatures($this->defaultFeatures());
        $this->options->setOptions($this->defaultOptions());

        $this->ajax->setOptions(array(
            'url' => $this->router->generate('truck_info_list_ajax'),
            'type' => 'GET'
        ));

        $twigVars = $this->twig->getGlobals();
        $dateFormat = isset($twigVars['js_moment_date_format']) ? $twigVars['js_moment_date_format'] : 'D-MM-YY';

        $this->columnBuilder
            ->add('createdAt', 'datetime', array('title' => 'Date', 'date_format' => $dateFormat))
            ->add('agent.user.username', 'column', array('title' => 'Agent Name'))
            ->add('driverName', 'column', array('title' => 'Driver Name'))
            ->add('driverPhone', 'column', array('title' => 'Driver Phone'))
            ->add('truckNumber', 'column', array('title' => 'Truck Number'))
            ->add('status', 'column', array('title' => 'Status'))
            ->add('orders.id', 'array', array(
                'title' => 'Orders',
                'data' => 'orders[, ].id'
            ))
            ->add('remark', 'column', array('title' => 'Remark'))
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getEntity()
    {
        return 'Rbs\Bundle\SalesBundle\Entity\TruckInfo';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'truck_info_datatable';
    }
}
