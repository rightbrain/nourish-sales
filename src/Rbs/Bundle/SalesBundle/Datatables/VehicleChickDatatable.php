<?php

namespace Rbs\Bundle\SalesBundle\Datatables;
use Rbs\Bundle\SalesBundle\Entity\Vehicle;

/**
 * Class VehicleChickDatatable
 *
 * @package Rbs\Bundle\SalesBundle\Datatables
 */
class VehicleChickDatatable extends BaseDatatable
{
    public function getLineFormatter()
    {
        /** @var Vehicle $vehicle
         * @return mixed
         */
        $formatter = function($line){

            $vehicle = $this->em->getRepository('RbsSalesBundle:Vehicle')->find($line['id']);
            $line['isDeliveryFalse'] = $vehicle->isDeliveryFalse();
            if(!empty($line['agent'])){
                $profile = $this->em->getRepository('RbsUserBundle:Profile')->findOneBy(array('user' => $line['agent']['user']['id']));
                $line["fullName"] = $profile->getFullName();
            }else{
                $line["fullName"] = 'NOURISH';
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
        $this->features->setFeatures($this->defaultFeatures());
        $this->options->setOptions(array_merge($this->defaultOptions(), array('order' => [[0, 'desc']])));

        $this->ajax->setOptions(array(
            'url' => $this->router->generate('chick_truck_info_list_ajax'),
            'type' => 'GET'
        ));

        $twigVars = $this->twig->getGlobals();
        $dateFormat = isset($twigVars['js_moment_date_format']) ? $twigVars['js_moment_date_format'] : 'D-MM-YY';

        $this->columnBuilder
            ->add('createdAt', 'datetime', array('title' => 'Date', 'date_format' => $dateFormat));
        $this->columnBuilder
            ->add('agent.user.id', 'column', array('title' => 'Agent/Nourish', 'render' => 'resolveAgentName'));
        $this->columnBuilder
            ->add('deliveries.id', 'column', array('title' => 'Delivery'))
            ->add('orderText', 'column', array('title' => 'Orders'))
            ->add('driverName', 'column', array('title' => 'Driver Name'))
            ->add('depo.name', 'column', array('title' => 'Depot Name'))
            ->add('driverPhone', 'column', array('title' => 'Driver Phone'))
            ->add('truckNumber', 'column', array('title' => 'Truck Number'))
            ->add('status', 'column', array('title' => 'Status'))
            ->add('isDeliveryFalse', 'virtual', array('visible' => false))
            ->add(null, 'action', array(
                'width' => '',
                'title' => 'Action',
                'start_html' => '<div class="wrapper">',
                'end_html' => '</div>',
                'actions' => array(
                    array(
                        'route' => 'chick_vehicle_view',
                        'route_parameters' => array(
                            'id' => 'id'
                        ),
                        'label' => 'View',
                        'icon' => 'glyphicon glyphicon-eye-open',
                        'attributes' => array(
                            'rel' => 'tooltip',
                            'title' => 'edit-action',
                            'class' => 'btn btn-primary btn-xs',
                            'role' => 'button'
                        ),
                        'render_if' => array('isDeliveryFalse'),
                        'role' => 'ROLE_USER',
                    ),
                    array(
                        'route' => 'chick_vehicle_challan',
                        'route_parameters' => array(
                            'id' => 'id'
                        ),
                        'label' => 'Challan',
                        'icon' => 'glyphicon glyphicon-print',
                        'attributes' => array(
                            'rel' => 'tooltip',
                            'title' => 'challan view',
                            'class' => 'btn btn-success btn-xs',
                            'role' => 'button'
                        ),
                        'render_if' => array('isDeliveryFalse'),
                        'role' => 'ROLE_USER',
                    ),
                    array(
                        'route' => 'chick_delivery_edit',
                        'route_parameters' => array(
                            'id' => 'deliveries.id'
                        ),
                        'label' => 'Edit',
                        'icon' => 'glyphicon glyphicon-edit',
                        'attributes' => array(
                            'rel' => 'tooltip',
                            'title' => 'edit-action',
                            'class' => 'btn btn-success btn-xs',
                            'role' => 'button'
                        ),
                        'render_if' => array('isDeliveryFalse'),
                        'role' => 'ROLE_SUPER_ADMIN',
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
        return 'truck_info_datatable';
    }
}
