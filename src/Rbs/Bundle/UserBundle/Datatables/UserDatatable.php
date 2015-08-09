<?php

namespace Rbs\Bundle\UserBundle\Datatables;

/**
 * Class UserDatatable
 *
 * @package Rbs\Bundle\UserBundle\Datatables
 */
class UserDatatable extends BaseDatatable
{
    /**
     * {@inheritdoc}
     */
    public function buildDatatable()
    {
        $this->features->setFeatures($this->defaultFeatures());
        $this->options->setOptions($this->defaultOptions());

        $this->ajax->setOptions(array(
            'url' => $this->router->generate('users_list_ajax'),
            'type' => 'GET'
        ));

        $this->columnBuilder
                ->add('profile.user.username', 'column', array('title' => 'User name',))
                ->add('profile.fullName', 'column', array('title' => 'FullName',))
                ->add('profile.cellphone', 'column', array('title' => 'Cellphone',))
                ->add('profile.designation', 'column', array('title' => 'Designation',))
                ->add(null, 'action', array(
                    'width' => '180px',
                    'title' => 'Update',
                    'start_html' => '<div class="wrapper">',
                    'end_html' => '</div>',
                    'actions' => array(
                        array(
                            'route' => 'user_update',
                            'route_parameters' => array(
                                'id' => 'id'
                            ),
                            'label' => 'Edit',
                            'icon' => 'glyphicon glyphicon-edit',
                            'attributes' => array(
                                'rel' => 'tooltip',
                                'title' => 'edit-action',
                                'class' => 'btn btn-primary btn-xs delete-list-btn',
                                'role' => 'button'
                            ),
                            'confirm' => false,
                            'confirm_message' => 'Are you sure?',
                            'role' => 'ROLE_ADMIN',
                        ),
                        array(
                            'route' => 'user_update_password',
                            'route_parameters' => array(
                                'id' => 'id'
                            ),
                            'label' => 'Edit pass',
                            'icon' => 'glyphicon glyphicon-edit',
                            'attributes' => array(
                                'rel' => 'tooltip',
                                'title' => 'edit-password-action',
                                'class' => 'btn btn-primary btn-xs delete-list-btn',
                                'role' => 'button'
                            ),
                            'confirm' => false,
                            'confirm_message' => 'Are you sure?',
                            'role' => 'ROLE_ADMIN',
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
                            'route' => 'user_delete',
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
                            'role' => 'ROLE_ADMIN',
                        ),
                        array(
                            'route' => 'user_enabled',
                            'route_parameters' => array(
                                'id' => 'id'
                            ),
                            'label' => 'Enable',
                            'icon' => 'glyphicon glyphicon-edit',
                            'attributes' => array(
                                'rel' => 'tooltip',
                                'title' => 'enable-action',
                                'class' => 'btn btn-primary btn-xs delete-list-btn',
                                'role' => 'button'
                            ),
                            'confirm' => false,
                            'confirm_message' => 'Are you sure?',
                            'role' => 'ROLE_ADMIN',
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
                            'route' => 'user_details',
                            'route_parameters' => array(
                                'id' => 'id'
                            ),
                            'label' => 'Details',
                            'icon' => 'glyphicon',
                            'attributes' => array(
                                'rel' => 'tooltip',
                                'title' => 'details-action',
                                'class' => 'btn btn-primary btn-xs',
                                'role' => 'button'
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
        return 'Rbs\Bundle\UserBundle\Entity\User';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'user_datatable';
    }
}
