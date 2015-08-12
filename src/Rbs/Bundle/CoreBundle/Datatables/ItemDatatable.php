<?php

namespace Rbs\Bundle\CoreBundle\Datatables;

/**
 * Class ItemDatatable
 *
 * @package Rbs\Bundle\CoreBundle\Datatables
 */
class ItemDatatable extends BaseDatatable
{
    /**
     * {@inheritdoc}
     */
    public function buildDatatable()
    {
        $this->features->setFeatures($this->defaultFeatures());
        $this->options->setOptions($this->defaultOptions());

        $this->ajax->setOptions(array(
            'url' => $this->router->generate('item_list_ajax'),
            'type' => 'GET'
        ));

        $this->columnBuilder
            ->add('name', 'column', array('title' => 'Name',))
            ->add('itemUnit', 'column', array('title' => 'Item Unit',))
            ->add('price', 'column', array('title' => 'Price',))
            ->add('itemType.itemType', 'column', array('title' => 'Item Type',))
            ->add('bundles.name', 'array', array('title' => 'Modules', 'data' => 'bundles[, ].name'))
            ->add(null, 'action', array(
                'width' => '180px',
                'title' => 'Action',
                'actions' => array(
                    $this->makeActionButton('item_edit', array('id' => 'id'), 'ROLE_ADMIN', 'Edit', 'Edit', 'fa fa-pencil-square-o'),
                    $this->makeActionButton('item_delete', array('id' => 'id'), 'ROLE_ADMIN', 'Delete', 'Delete', 'fa fa-trash-o', 'btn btn-default btn-xs delete-list-btn'),
                )
            ));
    }

    /**
     * {@inheritdoc}
     */
    public function getEntity()
    {
        return 'Rbs\Bundle\CoreBundle\Entity\Item';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'item_datatable';
    }
}
