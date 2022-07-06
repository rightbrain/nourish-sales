<?php

namespace Rbs\Bundle\SalesBundle\Datatables;

use Rbs\Bundle\SalesBundle\Entity\DeliveryPoint;
use Sg\DatatablesBundle\Datatable\View\AbstractDatatableView;
use Sg\DatatablesBundle\Datatable\View\Style;

/**
 * Class DeliveryPointDatatable
 *
 * @package Rbs\Bundle\SalesBundle\Datatables
 */
class DeliveryPointDatatable extends BaseDatatable
{
    public function getLineFormatter()
    {
        /** @var DeliveryPoint $deliveryPoint
         * @return mixed
         */
        $formatter = function($line){

            $deliveryPoint = $this->em->getRepository('RbsSalesBundle:DeliveryPoint')->find($line['id']);
            $line['isActive'] = $deliveryPoint->isActive();


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
            'url' => $this->router->generate('delivery_point_list_ajax'),
            'type' => 'GET'
        ));

        $this->columnBuilder
//                ->add('id', 'column', array('title' => 'Id',))
                ->add('pointAddress', 'column', array('title' => 'Delivery Point',))
                ->add('pointPhone', 'column', array('title' => 'Phone',))
                ->add('contactPerson', 'column', array('title' => 'Contact Person',))
                ->add('isActive', 'virtual', array('visible' => true, 'title'=>"Status"))
                ->add(null, 'action', array(
                'width' => '',
                'title' => 'Action',
                'start_html' => '<div class="wrapper">',
                'end_html' => '</div>',
                'actions' => array(
                    array(
                        'route' => 'delivery_point_edit',
                        'route_parameters' => array(
                            'id' => 'id'
                        ),
                        'label' => 'Edit',
                        'icon' => 'glyphicon glyphicon-edit',
                        'attributes' => array(
                            'rel' => 'tooltip',
                            'title' => 'edit-action',
                            'class' => 'btn btn-success btn-xs',
                        ),
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
        return 'Rbs\Bundle\SalesBundle\Entity\DeliveryPoint';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'deliverypoint_datatable';
    }
}
