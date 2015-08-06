<?php

namespace Rbs\Bundle\CoreBundle\Datatables;

/**
 * Class CategoryDatatable
 *
 * @package Rbs\Bundle\CoreBundle\Datatables
 */
class CategoryDatatable extends BaseDatatable
{
    /**
     * {@inheritdoc}
     */
    public function buildDatatable()
    {
        $this->features->setFeatures($this->defaultFeatures());
        $this->options->setOptions($this->defaultOptions());

        $this->ajax->setOptions(array(
            'url' => $this->router->generate('category_list_ajax'),
            'type' => 'GET'
        ));

        $this->columnBuilder
                ->add('name', 'column', array('title' => 'Name',))
                //->add('status', 'column', array('title' => 'Status',))
                ->add('createdAt', 'datetime', array('title' => 'Created At', 'date_format' => 'YYYY-MM-DD'))
                ->add(null, 'action', array(
                    'width' => '180px',
                    'title' => 'Action',
                    'start_html' => '<div class="wrapper">',
                    'end_html' => '</div>',
                    'actions' => array(
                        array(
                            'route' => 'category_edit',
                            'route_parameters' => array(
                                'id' => 'id'
                            ),
                            'label' => 'Edit',
                            'icon' => 'fa fa-pencil-square-o',
                            'attributes' => array(
                                'rel' => 'tooltip',
                                'title' => 'Edit',
                                'class' => 'btn btn-primary btn-xs',
                                'role' => 'button'
                            ),
                            'confirm' => false,
                            'confirm_message' => 'Are you sure?',
                            'role' => 'ROLE_ADMIN',
                        ),
                        array(
                            'route' => 'category_delete',
                            'route_parameters' => array(
                                'id' => 'id'
                            ),
                            'label' => 'Delete',
                            'icon' => 'fa fa-trash-o',
                            'attributes' => array(
                                'rel' => 'tooltip',
                                'title' => 'Delete',
                                'class' => 'btn btn-default btn-xs delete-list-btn',
                                'role' => 'button'
                            ),
                            'role' => 'ROLE_ADMIN',
                        ),
                    )
                ))
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getEntity()
    {
        return 'Rbs\Bundle\CoreBundle\Entity\Category';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'category_datatable';
    }
}
