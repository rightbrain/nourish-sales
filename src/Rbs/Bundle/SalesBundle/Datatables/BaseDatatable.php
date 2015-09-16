<?php

namespace Rbs\Bundle\SalesBundle\Datatables;

use Sg\DatatablesBundle\Datatable\View\AbstractDatatableView;
use Sg\DatatablesBundle\Datatable\View\Style;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Twig_Environment;

/**
 * Class BaseDatatable
 *
 * @package Rbs\Bundle\CoreBundle\Datatables
 */
class BaseDatatable extends AbstractDatatableView
{
    public function getStatusColor($status)
    {
        $color = 'info';
        switch ($status) {
            case 'PENDING':
                $color = 'warning';
                break;
            case 'PROCESSING':
            case 'PARTIALLY_PAID':
            case 'READY':
            case 'PARTIALLY_SHIPPED':
                $color = 'info';
                break;
            case 'HOLD':
            case 'APPROVAL':
                $color = 'warning';
                break;
            case 'CANCEL':
                $color = 'danger';
                break;
            case 'PAID':
            case 'COMPLETE':
            case 'SHIPPED':
                $color = 'success';
                break;
        }

        return $color;
    }
    public function defaultFeatures()
    {
        return array(
            'auto_width' => true,
            'defer_render' => false,
            'info' => true,
            'jquery_ui' => false,
            'length_change' => true,
            'ordering' => true,
            'paging' => true,
            'processing' => true,
            'scroll_x' => false,
            'scroll_y' => '',
            'searching' => true,
            'server_side' => true,
            'state_save' => false,
            'delay' => 0
        );
    }

    public function defaultOptions()
    {
        return array(
            'display_start' => 0,
            'defer_loading' => -1,
            'dom' => 'lfrtip',
            'length_menu' => array(10, 25, 50, 100),
            'order_classes' => true,
            'order' => [[0, 'asc']],
            'order_multi' => true,
            'page_length' => 10,
            'paging_type' => Style::FULL_NUMBERS_PAGINATION,
            'renderer' => '',
            'scroll_collapse' => false,
            'search_delay' => 0,
            'state_duration' => 7200,
            'stripe_classes' => array(),
            'responsive' => true,
            'class' => Style::BOOTSTRAP_3_STYLE,
            'individual_filtering' => false,
            'individual_filtering_position' => 'foot',
            'use_integration_options' => false
        );
    }

    public function getEntity(){}

    public function getName(){}
}
