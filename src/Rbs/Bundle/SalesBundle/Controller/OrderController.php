<?php

namespace Rbs\Bundle\SalesBundle\Controller;

use Rbs\Bundle\CoreBundle\Controller\BaseController;
use Rbs\Bundle\SalesBundle\Entity\Order;
use Rbs\Bundle\SalesBundle\Entity\OrderItem;
use Rbs\Bundle\SalesBundle\Event\OrderApproveEvent;
use Rbs\Bundle\SalesBundle\Form\Type\OrderForm;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use JMS\SecurityExtraBundle\Annotation as JMS;

/**
 * User Controller.
 *
 */
class OrderController extends BaseController
{
    /**
     * @Route("/orders", name="orders_home")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $request)
    {
        $datatable = $this->get('rbs_erp.sales.datatable.order');
        $datatable->buildDatatable();

        return $this->render('RbsSalesBundle:Order:index.html.twig', array(
            'datatable' => $datatable
        ));
    }

    /**
     * Lists all Category entities.
     *
     * @Route("/orders_list_ajax", name="orders_list_ajax", options={"expose"=true})
     * @Method("GET")
     */
    public function listAjaxAction()
    {
        $datatable = $this->get('rbs_erp.sales.datatable.order');
        $datatable->buildDatatable();

        $query = $this->get('sg_datatables.query')->getQueryFrom($datatable);

        return $query->getResponse();
    }

    /**
     * @Route("/order/create", name="order_create", options={"expose"=true})
     * @Template("RbsSalesBundle:Order:new.html.twig")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function createAction(Request $request)
    {
        $order = new Order();
        $form = $this->createForm(new OrderForm(), $order);

        if ('POST' === $request->getMethod()) {
            $form->handleRequest($request);

            if ($form->isValid()) {

                $em = $this->getDoctrine()->getManager();
                $em->getRepository('RbsSalesBundle:Order')->create($order);
                $em->getRepository('RbsSalesBundle:Stock')->addStockToOnHold($order);

                $this->flashMessage('success', 'Order Add Successfully!');

                return $this->redirect($this->generateUrl('orders_home'));
            }
        }

        return array(
            'form' => $form->createView(),
        );
    }

    /**
     * @Route("/order/update/{id}", name="order_update", options={"expose"=true})
     * @Template("RbsSalesBundle:Order:edit.html.twig")
     * @param Request $request
     * @param Order $order
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function updateAction(Request $request, Order $order)
    {
        $form = $this->createForm(new OrderForm(), $order);
        $em = $this->getDoctrine()->getManager();

        if ('POST' === $request->getMethod()) {

            $sms = $order->getRefSMS();
            $stockRepo = $em->getRepository('RbsSalesBundle:Stock');
            $oldQty = $stockRepo->extractOrderItemQuantity($order);
            $form->handleRequest($request);

            if ($form->isValid()) {

                if ($sms) {
                    $em->getRepository('RbsSalesBundle:Sms')->removeOrder($sms);
                }

                if ($order->getOrderState() != Order::ORDER_STATE_PENDING) {
                    $stockRepo->subtractFromOnHold($oldQty);
                }
                $stockRepo->addStockToOnHold($order);
                $em->getRepository('RbsSalesBundle:Order')->update($order, true);

                $this->flashMessage('success', 'Order Update Successfully!');

                return $this->redirect($this->generateUrl('orders_home'));
            }
        }

        return array(
            'form' => $form->createView(),
            'order' => $order
        );
    }

    /**
     * @Route("/order/details/{id}", name="order_details", options={"expose"=true})
     * @param Order $order
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function detailsAction(Order $order)
    {
        return $this->render('RbsSalesBundle:Order:details.html.twig', array(
            'order' => $order
        ));
    }

    /**
     * @JMS\Secure(roles="ROLE_ORDER_APPROVE")
     * @Route("/order/approve/{id}", name="order_approve", options={"expose"=true})
     * @param Order $order
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function orderApproveAction(Order $order)
    {
        if (!$this->isOrderValidState($order)) {
            return $this->redirectOnInvalidOrderState($order);
        }

        if ($order->getOrderState() == Order::ORDER_STATE_PENDING) {
            $this->getDoctrine()->getRepository('RbsSalesBundle:Stock')->addStockToOnHold($order);
        }

        $order->setOrderState(Order::ORDER_STATE_PROCESSING);
        $this->getDoctrine()->getRepository('RbsSalesBundle:Order')->update($order);

        $this->dispatch('order.approved', new OrderApproveEvent($order));

        $this->flashMessage('success', 'Order Approve Successfully!');

        return $this->redirect($this->generateUrl('orders_home'));
    }

    /**
     * @JMS\Secure(roles="ROLE_ORDER_CANCEL")
     * @Route("/order/cancel/{id}", name="order_cancel", options={"expose"=true})
     * @param Order $order
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function orderCancelAction(Order $order)
    {
        if (!$this->isOrderValidState($order)) {
            return $this->redirectOnInvalidOrderState($order);
        }

        if ($order->getOrderState() != Order::ORDER_STATE_PENDING) {
            $stockRepo = $this->getDoctrine()->getRepository('RbsSalesBundle:Stock');
            $oldQty = $stockRepo->extractOrderItemQuantity($order);
            $stockRepo->subtractFromOnHold($oldQty);
        }

        $order->setOrderState(Order::ORDER_STATE_CANCEL);

        $this->getDoctrine()->getRepository('RbsSalesBundle:Order')->update($order);

        $this->dispatch('order.canceled', new OrderApproveEvent($order));

        $this->flashMessage('success', 'Order Cancel Successfully!');

        return $this->redirect($this->generateUrl('orders_home'));
    }

    /**
     * @JMS\Secure(roles="ROLE_ORDER_APPROVE")
     * @Route("/order/hold/{id}", name="order_hold", options={"expose"=true})
     * @param Order $order
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function orderHoldAction(Order $order)
    {
        if (!$this->isOrderValidState($order)) {
            return $this->redirectOnInvalidOrderState($order);
        }

        if ($order->getOrderState() == Order::ORDER_STATE_PENDING) {
            $this->getDoctrine()->getRepository('RbsSalesBundle:Stock')->addStockToOnHold($order);
        }

        $order->setOrderState(Order::ORDER_STATE_HOLD);
        $this->getDoctrine()->getRepository('RbsSalesBundle:Order')->update($order);

        $this->dispatch('order.hold', new OrderApproveEvent($order));

        $this->flashMessage('success', 'Order Hold Successfully!');

        return $this->redirect($this->generateUrl('orders_home'));
    }

    /**
     * @JMS\Secure(roles="ROLE_ORDER_VIEW")
     * @Route("/order/summery/view/{id}", name="order_summery_view", options={"expose"=true})
     * @param Order $order
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function summeryViewAction(Order $order)
    {
        $stockRepo = $this->getDoctrine()->getRepository('RbsSalesBundle:Stock');
        /** @var OrderItem $item */
        foreach ($order->getOrderItems() as $item) {
            $stockItem = $stockRepo->findOneBy(array('item' => $item->getItem()->getId()));
            $item->isAvailable = $stockItem->isStockAvailable($item->getQuantity());
        }

        return $this->render('RbsSalesBundle:Order:summeryView.html.twig', array(
            'order' => $order
        ));
    }

    protected function isOrderValidState(Order $order)
    {
        if (in_array($order->getOrderState(), array(
            Order::ORDER_STATE_CANCEL,
            Order::ORDER_STATE_COMPLETE
        ))) {
            return false;
        }

        return true;
    }

    protected function redirectOnInvalidOrderState(Order $order)
    {
        $this->flashMessage('error', 'Order ' . $order->getId() . ' state is '. $order->getOrderState());

        return $this->redirect($this->generateUrl('orders_home'));
    }

    /**
     * @JMS\Secure(roles="ROLE_PAYMENT_APPROVE")
     * @Route("/order/{id}/payment-review", name="order_review_payment")
     * @param Order $order
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function paymentReviewAction(Order $order)
    {
        $customer = $order->getCustomer();
        $currentCreditLimit = $this->customerRepository()->getCurrentCreditLimit($customer);
        $isOverCredit = $currentCreditLimit < $order->getTotalAmount();

        return $this->render('RbsSalesBundle:Order:paymentReview.html.twig', array(
            'order' => $order,
            'isOverCredit' => $isOverCredit,
            'currentCreditLimit' => $currentCreditLimit
        ));
    }

    /**
     * @JMS\Secure(roles="ROLE_PAYMENT_APPROVE")
     * @Route("/order/{id}/approve-payment", name="order_approve_payment")
     * @param Order $order
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function paymentApproveAction(Order $order)
    {
        if ($order->getTotalAmount() === $order->getPaidAmount()) {
            $order->setPaymentState(Order::PAYMENT_STATE_PAID);
        } else {
            $order->setPaymentState(Order::PAYMENT_STATE_PARTIALLY_PAID);
        }
        $this->getDoctrine()->getRepository('RbsSalesBundle:Order')->update($order);

        $this->dispatch('payment.approve', new OrderApproveEvent($order));

        $this->flashMessage('success', 'Payment Approved Successfully!');

        return $this->redirect($this->generateUrl('orders_home'));
    }
}