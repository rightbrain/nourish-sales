<?php

namespace Rbs\Bundle\SalesBundle\Datatables;

use Rbs\Bundle\SalesBundle\Entity\Order;

/**
 * Class OrderDatatable
 *
 * @package Rbs\Bundle\SalesBundle\Datatables
 */
class OrderDatatable extends BaseDatatable
{
    public function getLineFormatter()
    {
        /** @var Order $order */
        $formatter = function($line){
            $order = $this->em->getRepository('RbsSalesBundle:Order')->find($line['id']);
            $line["isCancel"] = !$order->isCancel();
            $line["isComplete"] = !$order->isComplete();
            $line["enabled"] = $order->isPending();
            $line["disabled"] = !$order->isPending();
            $line["orderState"] = '<span class="label label-sm label-'.$this->getStatusColor($order->getOrderState()).'"> '.$order->getOrderState().' </span>';
            $line["paymentState"] = '<span class="label label-sm label-'.$this->getStatusColor($order->getPaymentState()).'"> '.$order->getPaymentState().' </span>';
            $line["deliveryState"] = '<span class="label label-sm label-'.$this->getStatusColor($order->getDeliveryState()).'"> '.$order->getDeliveryState().' </span>';
            $line["totalAmount"] = number_format($order->getTotalAmount(), 2);
            $line["paidAmount"] = number_format($order->getPaidAmount(), 2);

            $line["actionButtons"] = $this->generateActionList($order);

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
        $this->options->setOptions(array_merge($this->defaultOptions(), array(
            'individual_filtering' => true,
            'individual_filtering_position' => 'head'
        )));

        $this->ajax->setOptions(array(
            'url' => $this->router->generate('orders_list_ajax'),
            'type' => 'GET'
        ));

        $this->callbacks->setCallbacks(array
            (
                'pre_draw_callback' => "function( settings ) {
                        Order.filterInit();
                }"
            )
        );

        $twigVars = $this->twig->getGlobals();
        $dateFormat = isset($twigVars['js_moment_date_format']) ? $twigVars['js_moment_date_format'] : 'D-MM-YY';
        $this->columnBuilder
            ->add('id', 'column', array('title' => 'Order ID'))
            ->add('customer.user.username', 'column', array('title' => 'Customer'))
            ->add('createdAt', 'datetime', array('title' => 'Date', 'date_format' => $dateFormat))
            ->add('orderState', 'column', array('title' => 'Order State', 'render' => 'Order.OrderStateFormat'))
            ->add('paymentState', 'column', array('title' => 'Payment State', 'render' => 'Order.OrderStateFormat'))
            ->add('deliveryState', 'column', array('title' => 'Delivery State', 'render' => 'Order.OrderStateFormat'))
            ->add('totalAmount', 'column', array('title' => 'Total Amount', 'render' => 'Order.OrderPaymentFormat'))
            ->add('paidAmount', 'column', array('title' => 'Paid Amount', 'render' => 'Order.OrderPaymentFormat'))
            ->add('isComplete', 'virtual', array('visible' => false))
            ->add('isCancel', 'virtual', array('visible' => false))
            ->add('enabled', 'virtual', array('visible' => false))
            ->add('disabled', 'virtual', array('visible' => false))
            ->add('actionButtons', 'virtual', array('title' => 'Action'))
            /*->add(null, 'action', array(
                'width' => '200px',
                'title' => 'Action',
                'start_html' => '<div class="wrapper">',
                'end_html' => '</div>',
                'actions' => array(
                    array(
                        'route' => 'order_update',
                        'route_parameters' => array(
                            'id' => 'id'
                        ),
                        'label' => 'Edit',
                        'icon' => 'glyphicon glyphicon-edit',
                        'attributes' => array(
                            'rel' => 'tooltip',
                            'title' => 'edit-action',
                            'class' => 'btn btn-primary btn-xs',
                            'role' => 'button',
                        ),
                        'confirm' => false,
                        'confirm_message' => 'Are you sure?',
                        'role' => 'ROLE_ADMIN',
                        'render_if' => array('isComplete', 'isCancel')
                    )
                )
            ))
            ->add(null, 'action', array(
                'width' => '200px',
                'title' => 'Details/Summery',
                'start_html' => '<div class="wrapper">',
                'end_html' => '</div>',
                'actions' => array(
                    array(
                        'route' => 'order_summery_view',
                        'route_parameters' => array(
                            'id' => 'id'
                        ),
                        'label' => 'Summery View',
                        'icon' => 'glyphicon',
                        'attributes' => array(
                            'rel' => 'tooltip',
                            'title' => 'show-action',
                            'class' => 'btn btn-primary btn-xs green',
                            'role' => 'button',
                            'data-target' => "#ajaxSummeryView",
                            'data-toggle'=>"modal"
                        ),
                        'confirm' => false,
                        'confirm_message' => 'Are you sure?',
                        'render_if' => array('enabled')
                    ),
                    array(
                        'route' => 'order_details',
                        'route_parameters' => array(
                            'id' => 'id'
                        ),
                        'label' => 'Show Details',
                        'icon' => 'glyphicon',
                        'attributes' => array(
                            'rel' => 'tooltip',
                            'title' => 'show-action',
                            'class' => 'btn btn-primary btn-xs',
                            'role' => 'button'
                        ),
                        'confirm' => false,
                        'confirm_message' => 'Are you sure?',
                        'render_if' => array('disabled')
                    )
                )
            ))*/
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getEntity()
    {
        return 'Rbs\Bundle\SalesBundle\Entity\Order';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'order_datatable';
    }

    public function generateActionList(Order $order)
    {
        $canEdit = $this->authorizationChecker->isGranted('ROLE_ORDER_EDIT');
        $canView = $this->authorizationChecker->isGranted('ROLE_ORDER_VIEW');
        $canCancel = $this->authorizationChecker->isGranted('ROLE_ORDER_CANCEL');
        $canApproveOrder = $this->authorizationChecker->isGranted('ROLE_ORDER_APPROVE');
        $canApprovePayment = $this->authorizationChecker->isGranted('ROLE_PAYMENT_APPROVE');
        $canApproveOverCredit = $this->authorizationChecker->isGranted('ROLE_OVER_CREDIT_APPROVE');

        $html = '<div class="actions">
                <div class="btn-group">
                    <a class="btn btn-sm btn-default" href="javascript:;" data-toggle="dropdown" data-hover="dropdown" data-close-others="true" aria-expanded="false">
                    Actions <i class="fa fa-angle-down"></i>
                    </a>
                    <ul class="dropdown-menu pull-right">
                    ';

        if ($canEdit && !in_array($order->getOrderState(), array(Order::ORDER_STATE_COMPLETE, Order::ORDER_STATE_CANCEL))) {
            $html .= $this->generateMenuLink('Edit', 'order_update', array('id' => $order->getId()));
        }

        if ($canView) {
            $html .= $this->generateMenuLink('View', 'order_details', array('id' => $order->getId()));
        }

        if ($canCancel && !in_array($order->getOrderState(), array(Order::ORDER_STATE_COMPLETE, Order::ORDER_STATE_CANCEL))) {
            $html .= $this->generateMenuLink('Cancel', 'order_cancel', array('id' => $order->getId()));
        }

        if ($canApproveOrder && in_array($order->getOrderState(), array(Order::ORDER_STATE_PENDING))) {
            //$html .= $this->generateMenuLink('Approve Order', 'order_approve', array('id' => $order->getId()));
            $html .= '<li><a href="'.$this->router->generate('order_summery_view', array('id'=>$order->getId())).'" rel="tooltip" title="show-action" class="" role="button" data-target="#ajaxSummeryView" data-toggle="modal"><i class="glyphicon"></i> Approve Order</a></li>';
        }

        if ($canApprovePayment && in_array($order->getOrderState(), array(Order::ORDER_STATE_PROCESSING)) && in_array($order->getPaymentState(), array(Order::PAYMENT_STATE_PENDING))) {
            $html .= '<li><a href="'.$this->router->generate('order_review_payment', array('id'=>$order->getId())).'" rel="tooltip" title="show-action" class="" role="button" data-target="#ajaxSummeryView" data-toggle="modal"><i class="glyphicon"></i> Approve Payment</a></li>';
        }

        if ($canApproveOverCredit && in_array($order->getPaymentState(), array(Order::PAYMENT_STATE_CREDIT_APPROVAL))) {
            $html .= $this->generateMenuLink('Approve Credit', 'order_approve', array('id' => $order->getId()));
        }

            /*$html .= '
            <li>
                        <a href="javascript:;">
                            <i class="i"></i> All Project </a>
                        </li>
                        <li class="divider">
                        </li>
                        <li>
                            <a href="javascript:;">
                            AirAsia </a>
                        </li>
                        <li>
                            <a href="javascript:;">
                            Cruise </a>
                        </li>
                        <li>
                            <a href="javascript:;">
                            HSBC </a>
                        </li>
                        <li class="divider">
                        </li>
                        <li>
                            <a href="javascript:;">
                            Pending <span class="badge badge-danger">
                            4 </span>
                            </a>
                        </li>
                        <li>
                            <a href="javascript:;">
                            Completed <span class="badge badge-success">
                            12 </span>
                            </a>
                        </li>
                        <li>
                            <a href="javascript:;">
                            Overdue <span class="badge badge-warning">
                            9 </span>
                            </a>
                        </li>
            ';*/

            $html .='</ul>
                </div>
            </div>';

        return $html;
    }

    protected function generateMenuLink($label, $route = null, $param = array())
    {
        $link = !$route ? 'javascript:;' : $this->router->generate($route, $param);

        return sprintf('<li><a href="%s"> <i class="i"></i> %s </a> </li>', $link, $label);
    }
}
