<?php

namespace Rbs\Bundle\SalesBundle\Datatables;

use Sg\DatatablesBundle\Datatable\View\AbstractDatatableView;
use Sg\DatatablesBundle\Datatable\View\Style;

/**
 * Class VehicleNourishDatatable
 *
 * @package Rbs\Bundle\SalesBundle\Datatables
 */
class VehicleNourishDatatable extends BaseDatatable
{
    /**
     * {@inheritdoc}
     */
    public function buildDatatable()
    {
        $this->features->setFeatures($this->defaultFeatures());
        $this->options->setOptions(array_merge($this->defaultOptions(), array('order' => [[0, 'desc']])));

        $this->ajax->setOptions(array(
            'url' => $this->router->generate('nourish_truck_info_list_ajax'),
            'type' => 'GET'
        ));

        $this->columnBuilder
                ->add('id', 'column', array('title' => 'Id',))
                ->add('depo.name', 'column', array('title' => 'Depot Name'))
                ->add('driverName', 'column', array('title' => 'DriverName',))
                ->add('driverPhone', 'column', array('title' => 'DriverPhone',))
                ->add('truckNumber', 'column', array('title' => 'TruckNumber',))
                ->add(null, 'action', array(
                    'width' => '',
                    'title' => 'Action',
                    'start_html' => '<div class="wrapper">',
                    'end_html' => '</div>',
                    'actions' => array(
                        array(
                            'route' => 'nourish_truck_info_edit',
                            'route_parameters' => array(
                                'id' => 'id'
                            ),
                            'label' => 'Edit',
                            'icon' => 'glyphicon glyphicon-edit',
                            'attributes' => array(
                                'rel' => 'tooltip',
                                'title' => 'edit-action',
                                'class' => 'btn btn-success btn-xs',
                            )
//                            'render_if' => array('isOut')
    //                        'role' => 'ROLE_USER',
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
        return 'Rbs\Bundle\SalesBundle\Entity\VehicleNourish';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'vehiclenourish_datatable';
    }
}
