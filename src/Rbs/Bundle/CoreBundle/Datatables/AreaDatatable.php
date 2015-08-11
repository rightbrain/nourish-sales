<?php

namespace Rbs\Bundle\CoreBundle\Datatables;

/**
 * Class AreaDatatable
 *
 * @package Rbs\Bundle\CoreBundle\Datatables
 */
class AreaDatatable extends BaseDatatable
{
    /**
     * {@inheritdoc}
     */
    public function buildDatatable()
    {
        $this->features->setFeatures($this->defaultFeatures());
        $this->options->setOptions($this->defaultOptions());

        $this->ajax->setOptions(array(
            'url' => $this->router->generate('area_list_ajax'),
            'type' => 'GET'
        ));


        $this->columnBuilder
                ->add('areaName', 'column', array('title' => 'Area Name',))
                ->add('level1.name', 'column', array('title' => 'Zilla',))
                ->add('level2.name', 'column', array('title' => 'Thana',))
                ->add('level3.name', 'column', array('title' => 'Union',))
                ->add(null, 'action', array(
                    'width' => '180px',
                    'title' => 'Action',
                    'actions' => array(
                        $this->makeActionButton('area_edit', array('id' => 'id'), 'ROLE_ADMIN', 'Edit', 'Edit', 'fa fa-pencil-square-o'),
                        $this->makeActionButton('area_delete', array('id' => 'id'), 'ROLE_ADMIN', 'Delete', 'Delete', 'fa fa-trash-o', 'btn btn-default btn-xs delete-list-btn'),
                    )
                ))
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getEntity()
    {
        return 'Rbs\Bundle\CoreBundle\Entity\Area';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'area_datatable';
    }
}
