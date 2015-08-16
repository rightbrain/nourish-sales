<?php

namespace Rbs\Bundle\SalesBundle\Datatables;

use Rbs\Bundle\SalesBundle\Entity\Customer;

/**
 * Class CustomerDatatable
 *
 * @package Rbs\Bundle\SalesBundle\Datatables
 */
class CustomerDatatable extends BaseDatatable
{
    /**
     * {@inheritdoc}
     */
    public function buildDatatable()
    {
        $this->features->setFeatures($this->defaultFeatures());
        $this->options->setOptions($this->defaultOptions());

        $this->ajax->setOptions(array(
            'url' => $this->router->generate('customers_list_ajax'),
            'type' => 'GET'
        ));

        $this->columnBuilder
            ->add('user.username', 'column', array('title' => 'Customer name'))
            ->add(null, 'action', array(
                'width' => '180px',
                'title' => 'Update',
                'start_html' => '<div class="wrapper">',
                'end_html' => '</div>',
                'actions' => array(
                    array(
                        'route' => 'customer_update',
                        'route_parameters' => array(
                            'id' => 'id'
                        ),
                        'label' => 'Edit',
                        'icon' => 'glyphicon glyphicon-edit',
                        'attributes' => array(
                            'rel' => 'tooltip',
                            'title' => 'edit-action',
                            'class' => 'btn btn-primary btn-xs',
                            'role' => 'button'
                        ),
                        'confirm' => false,
                        'confirm_message' => 'Are you sure?',
                        'role' => 'ROLE_ADMIN',
                    ),
                    array(
                        'route' => 'customer_update_password',
                        'route_parameters' => array(
                            'id' => 'id'
                        ),
                        'label' => 'Edit pass',
                        'icon' => 'glyphicon glyphicon-edit',
                        'attributes' => array(
                            'rel' => 'tooltip',
                            'title' => 'edit-password-action',
                            'class' => 'btn btn-primary btn-xs',
                            'role' => 'button'
                        ),
                        'confirm' => false,
                        'confirm_message' => 'Are you sure?'
                    )
                )
            ))
            ->add(null, 'action', array(
                'width' => '180px',
                'title' => 'Action',
                'start_html' => '<div class="wrapper">',
                'end_html' => '</div>',
                'actions' => array(
                    array(
                        'route' => 'customer_delete',
                        'route_parameters' => array(
                            'id' => 'id'
                        ),
                        'label' => 'Delete',
                        'icon' => 'glyphicon',
                        'attributes' => array(
                            'rel' => 'tooltip',
                            'title' => 'delete-action',
                            'class' => 'btn btn-default btn-xs delete-list-btn',
                            'role' => 'button'
                        ),
                        'confirm' => false,
                        'confirm_message' => 'Are you sure?',
                        'role' => 'ROLE_ADMIN'
                    )
                )
            ))
            ->add(null, 'action', array(
                'width' => '180px',
                'title' => 'Show',
                'start_html' => '<div class="wrapper">',
                'end_html' => '</div>',
                'actions' => array(
                    array(
                        'route' => 'customer_details',
                        'route_parameters' => array(
                            'id' => 'id'
                        ),
                        'label' => 'Show',
                        'icon' => 'glyphicon',
                        'attributes' => array(
                            'rel' => 'tooltip',
                            'title' => 'show-action',
                            'class' => 'btn btn-default btn-xs',
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
        return 'Rbs\Bundle\SalesBundle\Entity\Customer';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'customer_datatable';
    }
}
