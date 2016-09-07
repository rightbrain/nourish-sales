<?php

namespace Rbs\Bundle\SalesBundle\Datatables;
use Rbs\Bundle\SalesBundle\Entity\TruckInfo;

/**
 * Class InOutTruckInfoDatatable
 *
 * @package Rbs\Bundle\SalesBundle\Datatables
 */
class InOutTruckInfoDatatable extends BaseDatatable
{
    public function getLineFormatter()
    {
        /** @var TruckInfo $truckInfo
         * @return mixed
         */
        $formatter = function($line){
            $truckInfo = $this->em->getRepository('RbsSalesBundle:TruckInfo')->find($line['id']);
            $line['isIn'] = $truckInfo->isIn();
            $line['isInFalse'] = !$truckInfo->isIn();
            $line['isOut'] = $truckInfo->isOut();
            $line['isOutFalse'] = !$truckInfo->isOut();
            $line['isStart'] = $truckInfo->isStart();
            $line['isStartFalse'] = !$truckInfo->isStart();
            $line['isFinish'] = $truckInfo->isFinish();
            $line['isFinishFalse'] = !$truckInfo->isFinish();

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
            'url' => $this->router->generate('truck_info_in_out_list_ajax'),
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
            ->add('orders.id', 'array', array(
                'title' => 'Orders',
                'data' => 'orders[, ].id'
            ))
            ->add('isIn', 'virtual', array('visible' => false))
            ->add('isOut', 'virtual', array('visible' => false))
            ->add('isStart', 'virtual', array('visible' => false))
            ->add('isFinish', 'virtual', array('visible' => false))
            ->add('isInFalse', 'virtual', array('visible' => false))
            ->add('isOutFalse', 'virtual', array('visible' => false))
            ->add('isStartFalse', 'virtual', array('visible' => false))
            ->add('isFinishFalse', 'virtual', array('visible' => false))
            ->add(null, 'action', array(
                'width' => '180px',
                'title' => 'Action',
                'start_html' => '<div class="wrapper">',
                'end_html' => '</div>',
                'actions' => array(
                    array(
                        'route' => 'truck_in',
                        'route_parameters' => array(
                            'id' => 'id'
                        ),
                        'label' => 'In Time',
                        'icon' => 'glyphicon glyphicon-edit',
                        'attributes' => array(
                            'rel' => 'tooltip',
                            'title' => 'enable-action',
                            'class' => 'btn btn-primary btn-xs delete-list-btn',
                            'role' => 'button'
                        ),
                        'confirm' => false,
                        'confirm_message' => 'Are you sure?',
                        'role' => 'ROLE_TRUCK_IN',
                        'render_if' => array('isIn')
                    ),
                    array(
                        'route' => 'truck_start',
                        'route_parameters' => array(
                            'id' => 'id'
                        ),
                        'label' => 'Start Load',
                        'icon' => 'glyphicon glyphicon-edit',
                        'attributes' => array(
                            'rel' => 'tooltip',
                            'title' => 'disable-action',
                            'class' => 'btn btn-primary btn-xs delete-list-btn',
                            'role' => 'button'
                        ),
                        'confirm' => false,
                        'confirm_message' => 'Are you sure?',
                        'role' => 'ROLE_TRUCK_START',
                        'render_if' => array('isStart', 'isInFalse')
                    ),
                    array(
                        'route' => 'truck_finish',
                        'route_parameters' => array(
                            'id' => 'id'
                        ),
                        'label' => 'Finish Load',
                        'icon' => 'glyphicon glyphicon-edit',
                        'attributes' => array(
                            'rel' => 'tooltip',
                            'title' => 'disable-action',
                            'class' => 'btn btn-primary btn-xs delete-list-btn',
                            'role' => 'button'
                        ),
                        'confirm' => false,
                        'confirm_message' => 'Are you sure?',
                        'role' => 'ROLE_TRUCK_FINISH',
                        'render_if' => array('isFinish', 'isStartFalse')
                    ),
                    array(
                        'route' => 'truck_out',
                        'route_parameters' => array(
                            'id' => 'id'
                        ),
                        'label' => 'Out Time',
                        'icon' => 'glyphicon glyphicon-edit',
                        'attributes' => array(
                            'rel' => 'tooltip',
                            'title' => 'disable-action',
                            'class' => 'btn btn-primary btn-xs delete-list-btn',
                            'role' => 'button'
                        ),
                        'confirm' => false,
                        'confirm_message' => 'Are you sure?',
                        'role' => 'ROLE_TRUCK_OUT',
                        'render_if' => array('isOut', 'isFinishFalse')
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
        return 'Rbs\Bundle\SalesBundle\Entity\TruckInfo';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'my_truck_info_datatable';
    }
}
