<?php

namespace Rbs\Bundle\CoreBundle\Datatables;

/**
 * Class CostHeaderDatatable
 *
 * @package Rbs\Bundle\CoreBundle\Datatables
 */
class CostHeaderDatatable extends BaseDatatable
{
    /**
     * {@inheritdoc}
     */
    public function buildDatatable()
    {
        $this->features->setFeatures($this->defaultFeatures());
        $this->options->setOptions($this->defaultOptions());

        $this->ajax->setOptions(array(
            'url' => $this->router->generate('cost_header_list_ajax'),
            'type' => 'GET'
        ));

        $this->columnBuilder
                ->add('title', 'column', array('title' => 'Title',))
                ->add('subCategory.subCategoryName', 'column', array('title' => 'Sub Category Name',))
                ->add(null, 'action', array(
                    'width' => '180px',
                    'title' => 'Action',
                    'actions' => array(
                        $this->makeActionButton('cost_header_edit', array('id' => 'id'), 'ROLE_ADMIN', 'Edit', 'Edit', 'fa fa-pencil-square-o'),
                        $this->makeActionButton('cost_header_delete', array('id' => 'id'), 'ROLE_ADMIN', 'Delete', 'Delete', 'fa fa-trash-o', 'btn btn-default btn-xs delete-list-btn'),
                    )
                ))
                ;
    }

    /**
     * {@inheritdoc}
     */
    public function getEntity()
    {
        return 'Rbs\Bundle\CoreBundle\Entity\CostHeader';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'costheader_datatable';
    }
}
