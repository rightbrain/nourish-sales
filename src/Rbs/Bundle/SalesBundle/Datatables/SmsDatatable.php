<?php

namespace Rbs\Bundle\SalesBundle\Datatables;

use Rbs\Bundle\SalesBundle\Entity\Sms;

/**
 * Class SmsDatatable
 *
 * @package Rbs\Bundle\SalesBundle\Datatables
 */
class SmsDatatable extends BaseDatatable
{
    /**
     * {@inheritdoc}
     */
    public function buildDatatable()
    {
        $this->features->setFeatures($this->defaultFeatures());
        $this->options->setOptions($this->defaultOptions());

        $this->ajax->setOptions(array(
            'url' => $this->router->generate('sms_list_ajax'),
            'type' => 'GET'
        ));

        $this->columnBuilder
            ->add('date', 'datetime', array(
                    'title' => 'Date',
                    'date_format' => 'LLL' ))
            ->add('mobileNo', 'column', array('title' => 'Mobile'))
            ->add('order', 'column', array('title' => 'Order Number'))
            ->add('remark', 'column', array('title' => 'SMS Text'))
            ->add('status', 'column', array('title' => 'Status'))
            ->add(null, 'action', array(
                'width' => '200px',
                'title' => 'Action',
                'start_html' => '<div class="wrapper">',
                'end_html' => '</div>',
                'actions' => array(
                    array(
                        'route' => 'order_create',
                        'route_parameters' => array(
                            'mobileNo' => 'mobileNo'
                        ),
                        'label' => 'Add Order',
                        'icon' => 'glyphicon glyphicon-edit',
                        'attributes' => array(
                            'rel' => 'tooltip',
                            'title' => 'add-action',
                            'class' => 'btn btn-primary btn-xs',
                            'role' => 'button',
                        ),
                        'confirm' => false,
                        'confirm_message' => 'Are you sure?',
                        'role' => 'ROLE_ADMIN',
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
        return 'Rbs\Bundle\SalesBundle\Entity\Sms';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'sms_datatable';
    }
}
