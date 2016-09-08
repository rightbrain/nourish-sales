<?php

namespace Rbs\Bundle\SalesBundle\Datatables;
use Rbs\Bundle\SalesBundle\Entity\Vehicle;

/**
 * Class VehicleWithDeliveryDatatable
 *
 * @package Rbs\Bundle\SalesBundle\Datatables
 */
class VehicleWithDeliveryDatatable extends BaseDatatable
{
    public function getLineFormatter()
    {
        /** @var Vehicle $vehicle
         * @return mixed
         */
        $formatter = function($line){
            $vehicle = $this->em->getRepository('RbsSalesBundle:Vehicle')->find($line['id']);

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
            'url' => $this->router->generate('truck_with_delivery_list_ajax'),
            'type' => 'GET'
        ));

        $this->columnBuilder
            ->add('driverName', 'column', array('title' => 'Driver Name'))
            ->add('driverPhone', 'column', array('title' => 'Driver Phone'))
            ->add('truckNumber', 'column', array('title' => 'Truck Number'))
            ->add('vehicleIn', 'datetime', array('title' => 'In Time', 'date_format' => 'LLL' ))
            ->add('startLoad', 'datetime', array('title' => 'Start Load', 'date_format' => 'LLL' ))
            ->add('finishLoad', 'datetime', array('title' => 'Finish Load', 'date_format' => 'LLL' ))
            ->add('vehicleOut', 'datetime', array('title' => 'Out Time', 'date_format' => 'LLL' ))
            ->add(null, 'action', array(
                'width' => '180px',
                'title' => 'Action',
                'start_html' => '<div class="wrapper">',
                'end_html' => '</div>',
                'actions' => array(
                    array(
                        'route' => 'set_truck_with_delivery',
                        'route_parameters' => array(
                            'id' => 'id'
                        ),
                        'label' => 'Add Delivery',
                        'icon' => 'glyphicon glyphicon-edit',
                        'attributes' => array(
                            'rel' => 'tooltip',
                            'title' => 'Add Delivery',
                            'class' => 'btn btn-primary btn-xs',
                            'role' => 'button'
                        )
                    )
                )
            ))
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getEntity()
    {
        return 'Rbs\Bundle\SalesBundle\Entity\Vehicle';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'my_truck_info_datatable';
    }
}
