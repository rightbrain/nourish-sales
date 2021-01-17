<?php

namespace Rbs\Bundle\SalesBundle\Datatables;

use Rbs\Bundle\SalesBundle\Entity\Agent;
use Rbs\Bundle\SalesBundle\Entity\Order;
use Rbs\Bundle\UserBundle\Entity\User;

/**
 * Class ChickOrderDatatable
 *
 * @package Rbs\Bundle\SalesBundle\Datatables
 */
class ChickOrderDatatable extends BaseDatatable
{
    private $user;
    protected $showAgentName;

    public function getLineFormatter()
    {
        /** @var Order $order
         * @return mixed
         */
        $formatter = function($line){
            $order = $this->em->getRepository('RbsSalesBundle:Order')->find($line['id']);
            $line["isCancel"] = !$order->isCancel();
            $line["isComplete"] = !$order->isComplete();
            $line["enabled"] = $order->isPending();
            $line["disabled"] = !$order->isPending();
            $line["checkDeliveryState"] = !$order->checkDeliveryState();
            $line["orderState"] = '<span class="label label-sm label-'.$this->getStatusColor($order->getOrderState()).'"> '.$order->getOrderState().' </span>';
            $line["paymentState"] = $order->getOrderState() == Order::ORDER_STATE_CANCEL ? '' : '<span class="label label-sm label-'.$this->getStatusColor($order->getPaymentState()).'"> '.$order->getPaymentState().' </span>';
            $line["deliveryState"] = $order->getOrderState() == Order::ORDER_STATE_CANCEL ? '' : '<span class="label label-sm label-'.$this->getStatusColor($order->getDeliveryState()).'"> '.$order->getDeliveryState().' </span>';
            $line["totalAmount"] = number_format($order->getTotalAmount(), 2);
            $line["paymentAmount"] = $order->getPaymentState() != Order::PAYMENT_STATE_PENDING ? number_format($order->getTotalPaymentDepositedAmount(), 2): number_format(0, 2);
            $line["actualAmount"] = $order->getPaymentState() != Order::PAYMENT_STATE_PENDING ? number_format($order->getTotalPaymentActualAmount(), 2): number_format(0, 2);
            if ($this->showAgentName) {
                $line["fullName"] = $order->getAgent()->getUser()->getProfile()->getFullName();
                $line["agentDistrict"] = $order->getAgent()->getUser()->getZilla()?$order->getAgent()->getUser()->getZilla()->getName():'';
            }

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
        /** @var User $user */
        $this->user = $this->securityToken->getToken()->getUser();
        $this->showAgentName = $this->user->getUserType() != User::AGENT;

        $this->features->setFeatures(array_merge($this->defaultFeatures(), array('state_save' => true)));
        $this->options->setOptions(array_merge($this->defaultOptions(), array(
            'individual_filtering' => true,
            'individual_filtering_position' => 'head',
            'order' => [[0, 'desc']],
        )));

        $this->ajax->setOptions(array(
            'url' => $this->router->generate('chick_orders_list_ajax'),
            'type' => 'GET'
        ));

        $this->callbacks->setCallbacks(array
            (
                'init_complete' => "function(settings) {
                        Order.filterInit();
                }",
                'pre_draw_callback' => "function(settings) {
                    $('.dataTables_scrollHead').find('table thead tr').eq(1).remove();
                }"
            )
        );

        $twigVars = $this->twig->getGlobals();
        $dateFormat = isset($twigVars['js_moment_date_format']) ? $twigVars['js_moment_date_format'] : 'D-MM-YY';
        $this->columnBuilder->add('id', 'column', array('title' => 'Order ID'));
        if ($this->showAgentName) {
            $this->columnBuilder->add('agent.chickAgentID', 'column', array('title' => 'Agent Id'));
            $this->columnBuilder->add('agent.user.id', 'column', array('title' => 'Agent Name', 'render' => 'resolveAgentName'));
            $this->columnBuilder->add('agentDistrict', 'virtual', array('title' => 'Agent District'));
        }
        $this->columnBuilder->add('depo.name', 'column', array('title' => 'Depot'))
            ->add('createdAt', 'datetime', array('title' => 'Date', 'date_format' => $dateFormat))
            ->add('orderState', 'column', array('title' => 'Order State', 'render' => 'Order.OrderStateFormat'))
            ->add('paymentState', 'column', array('title' => 'Payment State', 'render' => 'Order.OrderStateFormat'))
            ->add('deliveryState', 'column', array('title' => 'Delivery State', 'render' => 'Order.OrderStateFormat'))
            ->add('totalAmount', 'column', array('title' => 'Product Amount', 'render' => 'Order.OrderPaymentFormat'))
            ->add('paymentAmount', 'virtual', array('title' => 'Payment Amount', 'render' => 'Order.OrderPaymentFormat'))
            ->add('actualAmount', 'virtual', array('title' => 'Actual Amount', 'render' => 'Order.OrderPaymentFormat'))
            ->add('isComplete', 'virtual', array('visible' => false))
            ->add('isCancel', 'virtual', array('visible' => false))
            ->add('enabled', 'virtual', array('visible' => false))
            ->add('disabled', 'virtual', array('visible' => false))
            ->add('actionButtons', 'virtual', array('title' => 'Action'))
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
        $canEdit = $this->authorizationChecker->isGranted('ROLE_CHICK_ORDER_MANAGE');
        $canView = $this->authorizationChecker->isGranted('ROLE_CHICK_ORDER_MANAGE');
        $canCancel = $this->authorizationChecker->isGranted('ROLE_CHICK_ORDER_MANAGE');
        $canApproveOrder = $this->authorizationChecker->isGranted('ROLE_ORDER_APPROVE');
        $canApprovePayment = $this->authorizationChecker->isGranted('ROLE_PAYMENT_APPROVE');
        $canApproveOverCredit = $this->authorizationChecker->isGranted('ROLE_PAYMENT_OVER_CREDIT_APPROVE');
        $canOrderVerify = $this->authorizationChecker->isGranted('ROLE_ORDER_VERIFY');

        $html = '<div class="actions">
                <div class="btn-group">
                    <a class="btn btn-sm btn-default" href="javascript:;" data-toggle="dropdown" data-hover="dropdown" data-close-others="true" aria-expanded="false">
                    Actions <i class="fa fa-angle-down"></i>
                    </a>
                    <ul class="dropdown-menu pull-right">
                    ';

//        if($order->getDeliveryState() != Order::DELIVERY_STATE_READY and $order->getDeliveryState() != Order::DELIVERY_STATE_PARTIALLY_SHIPPED and $order->getDeliveryState() != Order::ORDER_STATE_CANCEL) {
        if ($canEdit && !in_array($order->getOrderState(), array(Order::ORDER_STATE_CANCEL))) {
            $route = 'chick_order_edit';
            if($order->getOrderVia()=='ONLINE'){
                $route= 'chick_order_edit';
            }
            $html .= $this->generateMenuLink('Edit', $route, array('id' => $order->getId()));
        }

        if ($canView) {
            $html .= $this->generateMenuLink('View', 'chick_order_details', array('id' => $order->getId()));
        }

        if ($canCancel && !in_array($order->getOrderState(), array(Order::ORDER_STATE_CANCEL))) {
//            $html .= $this->generateMenuLink('Cancel', 'chick_order_cancel', array('id' => $order->getId()));
            $html .= '<li><a href="'.$this->router->generate('chick_order_cancel', array('id'=>$order->getId())).'" rel="tooltip" title="Order Cancel" class="confirmation-btn" data-title="Do you want to cancel?"><i class="glyphicon"></i> Cancel</a></li>';
        }

        if ($canOrderVerify && $order->getOrderState() == Order::ORDER_STATE_PROCESSING
            && in_array($order->getPaymentState(), array(Order::PAYMENT_STATE_PAID, Order::PAYMENT_STATE_PARTIALLY_PAID))
            && in_array($order->getDeliveryState(), array(Order::DELIVERY_STATE_PENDING))
        ) {
            $html .= '<li><a href="'.$this->router->generate('order_review', array('id'=>$order->getId())).'" rel="tooltip" title="show-action" class="" role="button" data-target="#ajaxSummeryView" data-toggle="modal"><i class="glyphicon"></i> Verify Order</a></li>';
        }
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
