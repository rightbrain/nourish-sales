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
