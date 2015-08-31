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
            ->add('id', 'column', array('title' => 'Order ID'))
//            ->add(null, 'action', array(
//                'width' => '200px',
//                'title' => 'Action',
//                'start_html' => '<div class="wrapper">',
//                'end_html' => '</div>',
//                'actions' => array(
//                    array(
//                        'route' => 'stock_create',
//                        'route_parameters' => array(
//                            'id' => 'id'
//                        ),
//                        'label' => 'Add Stock',
//                        'icon' => 'glyphicon glyphicon-edit',
//                        'attributes' => array(
//                            'rel' => 'tooltip',
//                            'title' => 'edit-action',
//                            'class' => 'btn btn-primary btn-xs',
//                            'role' => 'button',
//                            'data-target' => "#ajax",
//                            'data-toggle'=>"modal"
//                        ),
//                        'confirm' => false,
//                        'confirm_message' => 'Are you sure?',
//                        'role' => 'ROLE_ADMIN',
//                    ),
//                    array(
//                        'route' => 'stock_history',
//                        'route_parameters' => array(
//                            'id' => 'id'
//                        ),
//                        'label' => 'History',
//                        'icon' => 'glyphicon',
//                        'attributes' => array(
//                            'rel' => 'tooltip',
//                            'title' => 'show-action',
//                            'class' => 'btn btn-primary btn-xs',
//                            'role' => 'button',
//                            'data-target' => "#ajaxHistory",
//                            'data-toggle'=>"modal"
//                        ),
//                        'confirm' => false,
//                        'confirm_message' => 'Are you sure?',
//                        'role' => 'ROLE_ADMIN',
//                    )
//                )
//            ))
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
