<?php

namespace Rbs\Bundle\SalesBundle\Datatables;

use Rbs\Bundle\SalesBundle\Entity\Order;

/**
 * Class OrderDatatable
 *
 * @package Rbs\Bundle\SalesBundle\Datatables
 */
class OrderDatatable extends BaseDatatable
{
    public function getLineFormatter()
    {
        /** @var Order $order */
        $formatter = function($line){
            $order = $this->em->getRepository('RbsSalesBundle:Order')->find($line['id']);
            $line["isCancel"] = !$order->isCancel();
            $line["isComplete"] = !$order->isComplete();
            $line["enabled"] = $order->isPending();
            $line["disabled"] = !$order->isPending();

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
        $this->options->setOptions(array_merge($this->defaultOptions(), array(
            'individual_filtering' => true,
            'individual_filtering_position' => 'head'
        )));

        $this->ajax->setOptions(array(
            'url' => $this->router->generate('orders_list_ajax'),
            'type' => 'GET'
        ));

        $this->callbacks->setCallbacks(array
            (
                'pre_draw_callback' => "function( settings ) {
                        Order.filterInit();
                }"
            )
        );

        $this->columnBuilder
            ->add('id', 'column', array('title' => 'OrderID'))
            ->add('customer.user.username', 'column', array('title' => 'Customer'))
            ->add('orderState', 'column', array('title' => 'Order'))
            ->add('paymentState', 'column', array('title' => 'Payment'))
            ->add('deliveryState', 'column', array('title' => 'Delivery'))
            ->add('totalAmount', 'column', array('title' => 'Total Amount'))
            ->add('paidAmount', 'column', array('title' => 'Paid Amount'))
            ->add('isComplete', 'virtual', array('visible' => false))
            ->add('isCancel', 'virtual', array('visible' => false))
            ->add('enabled', 'virtual', array('visible' => false))
            ->add('disabled', 'virtual', array('visible' => false))
            ->add(null, 'action', array(
                'width' => '200px',
                'title' => 'Action',
                'start_html' => '<div class="wrapper">',
                'end_html' => '</div>',
                'actions' => array(
                    array(
                        'route' => 'order_update',
                        'route_parameters' => array(
                            'id' => 'id'
                        ),
                        'label' => 'Edit',
                        'icon' => 'glyphicon glyphicon-edit',
                        'attributes' => array(
                            'rel' => 'tooltip',
                            'title' => 'edit-action',
                            'class' => 'btn btn-primary btn-xs',
                            'role' => 'button',
                        ),
                        'confirm' => false,
                        'confirm_message' => 'Are you sure?',
                        'role' => 'ROLE_ADMIN',
                        'render_if' => array('isComplete', 'isCancel')
                    )
                )
            ))
            ->add(null, 'action', array(
                'width' => '200px',
                'title' => 'Details/Summery',
                'start_html' => '<div class="wrapper">',
                'end_html' => '</div>',
                'actions' => array(
                    array(
                        'route' => 'order_summery_view',
                        'route_parameters' => array(
                            'id' => 'id'
                        ),
                        'label' => 'Summery View',
                        'icon' => 'glyphicon',
                        'attributes' => array(
                            'rel' => 'tooltip',
                            'title' => 'show-action',
                            'class' => 'btn btn-primary btn-xs green',
                            'role' => 'button',
                            'data-target' => "#ajaxSummeryView",
                            'data-toggle'=>"modal"
                        ),
                        'confirm' => false,
                        'confirm_message' => 'Are you sure?',
                        'render_if' => array('enabled')
                    ),
                    array(
                        'route' => 'order_details',
                        'route_parameters' => array(
                            'id' => 'id'
                        ),
                        'label' => 'Show Details',
                        'icon' => 'glyphicon',
                        'attributes' => array(
                            'rel' => 'tooltip',
                            'title' => 'show-action',
                            'class' => 'btn btn-primary btn-xs',
                            'role' => 'button'
                        ),
                        'confirm' => false,
                        'confirm_message' => 'Are you sure?',
                        'render_if' => array('disabled')
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
        return 'Rbs\Bundle\SalesBundle\Entity\Order';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'order_datatable';
    }
}
