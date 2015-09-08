<?php

namespace Rbs\Bundle\SalesBundle\Datatables;

use Rbs\Bundle\SalesBundle\Entity\Stock;

/**
 * Class CustomerDatatable
 *
 * @package Rbs\Bundle\SalesBundle\Datatables
 */
class StockDatatable extends BaseDatatable
{
    public function getLineFormatter()
    {
        /** @var Stock $stock */
        $formatter = function($line){
            $stock = $this->em->getRepository('RbsSalesBundle:Stock')->find($line['id']);
            $line['available'] = $stock->isAvailableOnDemand();
            $line['notAvailable'] = !$stock->isAvailableOnDemand();

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
            'url' => $this->router->generate('stocks_list_ajax'),
            'type' => 'GET'
        ));

        $this->columnBuilder
            ->add('item.name', 'column', array('title' => 'Item name'))
            ->add('onHand', 'column', array('title' => 'On Hand'))
            ->add('onHold', 'column', array('title' => 'On Hold'))
            ->add('available', 'virtual', array('visible' => false))
            ->add('notAvailable', 'virtual', array('visible' => false))
            ->add(null, 'action', array(
                'width' => '200px',
                'title' => 'Action',
                'start_html' => '<div class="wrapper">',
                'end_html' => '</div>',
                'actions' => array(
                    array(
                        'route' => 'stock_create',
                        'route_parameters' => array(
                            'id' => 'id'
                        ),
                        'label' => 'Add Stock',
                        'icon' => 'glyphicon glyphicon-edit',
                        'attributes' => array(
                            'rel' => 'tooltip',
                            'title' => 'edit-action',
                            'class' => 'btn btn-primary btn-xs',
                            'role' => 'button',
                            'data-target' => "#ajax",
                            'data-toggle'=>"modal"
                        ),
                        'confirm' => false,
                        'confirm_message' => 'Are you sure?',
                        'role' => 'ROLE_ADMIN',
                    ),
                    array(
                        'route' => 'stock_history',
                        'route_parameters' => array(
                            'id' => 'id'
                        ),
                        'label' => 'History',
                        'icon' => 'glyphicon',
                        'attributes' => array(
                            'rel' => 'tooltip',
                            'title' => 'show-action',
                            'class' => 'btn btn-primary btn-xs',
                            'role' => 'button',
                            'data-target' => "#ajaxHistory",
                            'data-toggle'=>"modal"
                        ),
                        'confirm' => false,
                        'confirm_message' => 'Are you sure?',
                        'role' => 'ROLE_ADMIN',
                    )
                )
            ))
            ->add(null, 'action', array(
                'width' => '200px',
                'title' => 'Available On Demand',
                'start_html' => '<div class="wrapper">',
                'end_html' => '</div>',
                'actions' => array(
                    array(
                        'route' => 'stock_available',
                        'route_parameters' => array(
                            'id' => 'id'
                        ),
                        'label' => 'Enable',
                        'icon' => 'glyphicon',
                        'attributes' => array(
                            'rel' => 'tooltip',
                            'title' => 'available-action',
                            'class' => 'btn btn-primary btn-xs green',
                            'role' => 'button',
                        ),
                        'confirm' => false,
                        'confirm_message' => 'Are you sure?',
                        'role' => 'ROLE_ADMIN',
                        'render_if' => array('notAvailable')
                    ),
                    array(
                        'route' => 'stock_available',
                        'route_parameters' => array(
                            'id' => 'id'
                        ),
                        'label' => 'Disable',
                        'icon' => 'glyphicon',
                        'attributes' => array(
                            'rel' => 'tooltip',
                            'title' => 'not-available-action',
                            'class' => 'btn btn-primary btn-xs red',
                            'role' => 'button',
                        ),
                        'confirm' => false,
                        'confirm_message' => 'Are you sure?',
                        'role' => 'ROLE_ADMIN',
                        'render_if' => array('available')
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
        return 'Rbs\Bundle\SalesBundle\Entity\Stock';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'stock_datatable';
    }
}
