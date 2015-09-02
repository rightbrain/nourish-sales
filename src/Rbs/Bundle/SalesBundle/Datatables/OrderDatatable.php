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
    /**
     * {@inheritdoc}
     */
    public function buildDatatable()
    {
        $this->features->setFeatures($this->defaultFeatures());
        $this->options->setOptions($this->defaultOptions());

        $this->ajax->setOptions(array(
            'url' => $this->router->generate('orders_list_ajax'),
            'type' => 'GET'
        ));

        $this->columnBuilder
            ->add('id', 'column', array('title' => 'OrderID'))
            ->add('customer.user.username', 'column', array('title' => 'Customer'))
            ->add('customer.user.username', 'column', array('title' => 'Customer'))
            ->add('deliveryState', 'column', array('title' => 'Delivery'))
            ->add('paymentState', 'column', array('title' => 'Payment'))
            ->add('orderState', 'column', array('title' => 'Order'))
            ->add('totalAmount', 'column', array('title' => 'Total Amount'))
            ->add('paidAmount', 'column', array('title' => 'Paid Amount'))
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
                    ),
                    array(
                        'route' => 'order_details',
                        'route_parameters' => array(
                            'id' => 'id'
                        ),
                        'label' => 'Show',
                        'icon' => 'glyphicon',
                        'attributes' => array(
                            'rel' => 'tooltip',
                            'title' => 'show-action',
                            'class' => 'btn btn-primary btn-xs',
                            'role' => 'button'
                        ),
                        'confirm' => false,
                        'confirm_message' => 'Are you sure?'
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
