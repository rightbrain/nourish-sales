<?php

namespace Rbs\Bundle\SalesBundle\Controller;

use Doctrine\ORM\QueryBuilder;
use Rbs\Bundle\SalesBundle\Entity\AgentsBankInfo;
use Rbs\Bundle\SalesBundle\Entity\Order;
use Rbs\Bundle\SalesBundle\Entity\OrderItem;
use Rbs\Bundle\SalesBundle\Event\OrderApproveEvent;
use Rbs\Bundle\SalesBundle\Form\Type\AgentsBankInfoForm;
use Rbs\Bundle\SalesBundle\Form\Type\OrderForm;
use Rbs\Bundle\UserBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use JMS\SecurityExtraBundle\Annotation as JMS;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Order Controller.
 *
 */
class OrderController extends BaseController
{
    /**
     * @Route("/orders", name="orders_home")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @JMS\Secure(roles="ROLE_AGENT, ROLE_AGENT, ROLE_ORDER_VIEW, ROLE_ORDER_CREATE, ROLE_ORDER_EDIT, ROLE_ORDER_APPROVE, ROLE_ORDER_CANCEL")
     */
    public function indexAction(Request $request)
    {
        $datatable = $this->get('rbs_erp.sales.datatable.order');
        $datatable->buildDatatable();

        return $this->render('RbsSalesBundle:Order:index.html.twig', array(
            'datatable' => $datatable,
        ));
    }

    /**
     * @Route("/orders/my", name="orders_my_home")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function myOrdersAction(Request $request)
    {
        $datatable = $this->get('rbs_erp.sales.datatable.order.individual');
        $datatable->buildDatatable();

        return $this->render('RbsSalesBundle:Order:my.html.twig', array(
            'datatable' => $datatable,
        ));
    }
    /**
     * Lists all Category entities.
     *
     * @Route("/orders_list_my_ajax", name="orders_list_my_ajax", options={"expose"=true})
     * @Method("GET")
     */
    public function listAjaxMyAction()
    {
        /** @var User $user */
        $user = $this->getUser();
        $agentRepository = $this->getDoctrine()->getRepository('RbsSalesBundle:Agent');
        $datatable = $this->get('rbs_erp.sales.datatable.order.individual');
        $datatable->buildDatatable();

        $query = $this->get('sg_datatables.query')->getQueryFrom($datatable);

        /** @var QueryBuilder $qb */
        $function = function($qb) use ($user, $agentRepository)
        {
            $qb->join('orders.agent', 'a');
            $qb->join('a.user', 'u');
            $qb->andWhere('u.id =:user');
            $qb->setParameter('user', $this->getUser()->getId());
        };
        $query->addWhereAll($function);

        return $query->getResponse();
    }

    /**
     * @Route("/orders/my/bank-info", name="orders_my_bank_info", options={"expose"=true})
     * @Template("RbsSalesBundle:Agent:bank_slip_upload.html.twig")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function agentBankInfoCreateAction(Request $request)
    {
        $agentsBankInfo = new AgentsBankInfo();
        $form = $this->createForm(new AgentsBankInfoForm($this->getUser()), $agentsBankInfo, array(
            'action' => $this->generateUrl('orders_my_bank_info'), 'method' => 'POST',
            'attr' => array('novalidate' => 'novalidate')
        ));

        if ('POST' === $request->getMethod()) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $agentsBankInfo->setAgent($this->getUser());
                $this->getDoctrine()->getManager()->getRepository('RbsSalesBundle:AgentsBankInfo')->create($agentsBankInfo);
                $this->flashMessage('success', 'Bank info add Successfully!');
                return $this->redirect($this->generateUrl('orders_my_home'));
            }
        }
        
        return array(
            'form' => $form->createView()
        );
    }
    
    /**
     * Lists all Category entities.
     *
     * @Route("/orders_list_ajax", name="orders_list_ajax", options={"expose"=true})
     * @Method("GET")
     * @JMS\Secure(roles="ROLE_AGENT, ROLE_AGENT, ROLE_ORDER_VIEW, ROLE_ORDER_CREATE, ROLE_ORDER_EDIT, ROLE_ORDER_APPROVE, ROLE_ORDER_CANCEL")
     */
    public function listAjaxAction()
    {
        /** @var User $user */
        $user = $this->getUser();
        $agentRepository = $this->getDoctrine()->getRepository('RbsSalesBundle:Agent');
        $datatable = $this->get('rbs_erp.sales.datatable.order');
        $datatable->buildDatatable();

        $query = $this->get('sg_datatables.query')->getQueryFrom($datatable);

        /** @var QueryBuilder $qb */
        $function = function($qb) use ($user, $agentRepository)
        {
            if ($user->getUserType() == User::AGENT) {
                $agent = $agentRepository->findOneBy(array('user' => $user->getId()));
                $qb->andWhere('orders.agent = :agent')->setParameter('agent', array($agent));
            } else if ($user->getUserType() == User::AGENT) {
                $agents = $agentRepository->findBy(array('agent' => $user->getId()));
                $qb->andWhere('orders.agent IN(:agents)')->setParameter('agents', $agents);
            }
        };
        $query->addWhereAll($function);

        return $query->getResponse();
    }

    /**
     * @Route("/order/readable/sms", name="order_readable_sms")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @JMS\Secure(roles="ROLE_AGENT, ROLE_AGENT, ROLE_ORDER_VIEW, ROLE_ORDER_CREATE, ROLE_ORDER_EDIT, ROLE_ORDER_APPROVE, ROLE_ORDER_CANCEL")
     */
    public function indexOrderReadableSmsAction(Request $request)
    {
        $datatable = $this->get('rbs_erp.sales.datatable.order.readable.sms');
        $datatable->buildDatatable();

        return $this->render('RbsSalesBundle:Order:readable.html.twig', array(
            'datatable' => $datatable,
        ));
    }
    
    /**
     * Lists all Order entities.
     *
     * @Route("/order_readable_sms_list_ajax", name="order_readable_sms_list_ajax", options={"expose"=true})
     * @Method("GET")
     * @JMS\Secure(roles="ROLE_AGENT, ROLE_AGENT, ROLE_ORDER_VIEW, ROLE_ORDER_CREATE, ROLE_ORDER_EDIT, ROLE_ORDER_APPROVE, ROLE_ORDER_CANCEL")
     */
    public function listAjaxOrderReadableSmsAction()
    {
        $datatable = $this->get('rbs_erp.sales.datatable.order.readable.sms');
        $datatable->buildDatatable();

        $query = $this->get('sg_datatables.query')->getQueryFrom($datatable);
        /** @var QueryBuilder $qb */
        $function = function($qb)
        {
            $qb->join('orders.agent', 'u');
            $qb->andWhere('u =:user');
            $qb->setParameter('user', $this->getUser());
        };
        $query->addWhereAll($function);

        return $query->getResponse();
    }

    /**
     * @Route("/order/create", name="order_create", options={"expose"=true})
     * @Template("RbsSalesBundle:Order:new.html.twig")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @JMS\Secure(roles="ROLE_ORDER_CREATE, ROLE_ORDER_EDIT, ROLE_ORDER_APPROVE")
     */
    public function createAction(Request $request)
    {
        $order = new Order();

        if ($request->query->get('sms')) {
            $refSms = $request->query->get('sms');
        } else {
            $refSms = 0;
        }

        $form = $this->createForm(new OrderForm($refSms), $order);

        if ('POST' === $request->getMethod()) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $order->setArea($order->getAgent()->getArea());
                $this->orderRepository()->create($order);
                $em->getRepository('RbsSalesBundle:Stock')->addStockToOnHold($order, $order->getAgent()->getDepo());

                $this->deliveryRepository()->createDelivery($order, $order->getAgent()->getDepo());

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
     * @JMS\Secure(roles="ROLE_ORDER_EDIT, ROLE_ORDER_APPROVE, ROLE_ORDER_CANCEL")
     */
    public function updateAction(Request $request, Order $order)
    {
        if($request->query->get('sms')){
            $refSms = $request->query->get('sms');
        }else{ $refSms = 0; }

        $form = $this->createForm(new OrderForm($refSms), $order);
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
                $stockRepo->addStockToOnHold($order, $order->getAgent()->getDepo());
                $em->getRepository('RbsSalesBundle:Order')->update($order, true);

                $this->flashMessage('success', 'Order Update Successfully!');

                return $this->redirect($this->generateUrl('orders_home'));
            }
        }

        return array(
            'form' => $form->createView(),
            'order' => $order,
        );
    }

    /**
     * @Route("/order/details/{id}", name="order_details", options={"expose"=true})
     * @param Order $order
     * @return \Symfony\Component\HttpFoundation\Response
     * @JMS\Secure(roles="ROLE_AGENT, ROLE_AGENT, ROLE_ORDER_VIEW, ROLE_ORDER_CREATE, ROLE_ORDER_EDIT, ROLE_ORDER_APPROVE, ROLE_ORDER_CANCEL")
     */
    public function detailsAction(Order $order)
    {
        $this->checkViewOrderAccess($order);

        $deliveryItems = $this->getDoctrine()->getRepository('RbsSalesBundle:DeliveryItem')->findBy(array(
            'order' => $order->getId()
        ));

        $deliveredItems = $this->getDoctrine()->getRepository('RbsSalesBundle:DeliveryItem')->getDeliveredItems($order);

        $auditLogs = $this->getDoctrine()->getRepository('RbsCoreBundle:AuditLog')->getByTypeOrObjectId(array(
            'order.verified', 'order.hold', 'order.canceled', 'payment.approved', 'payment.over.credit.approved'), $order->getId());

        return $this->render('RbsSalesBundle:Order:details.html.twig', array(
            'order' => $order,
            'deliveryItems' => $deliveryItems,
            'deliveredItems' => $deliveredItems,
            'auditLogs' => $auditLogs,
        ));
    }

    protected function checkViewOrderAccess(Order $order)
    {
        if ($this->isGranted('ROLE_AGENT') && $order->getAgent()->getUser()->getId() != $this->getUser()->getId()) {
            throw new AccessDeniedException('Access Denied');
        }

        if ($this->isGranted('ROLE_AGENT')) {
            $isOwnAgent = $this->agentRepository()->findOneBy(array(
                'agent' => $this->getUser(),
                'id' => $order->getAgent()->getId()
            ));
            if (!$isOwnAgent) {
                throw new AccessDeniedException('Access Denied');
            }
        }
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
            $this->getDoctrine()->getRepository('RbsSalesBundle:Stock')->addStockToOnHold($order, $order->getAgent()->getDepo());
        }

        $order->setOrderState(Order::ORDER_STATE_PROCESSING);
        $this->getDoctrine()->getRepository('RbsSalesBundle:Order')->update($order);

        $this->dispatchApproveProcessEvent('order.approved', $order);

        $this->flashMessage('success', 'Order Approve Successfully!');

        return $this->redirect($this->generateUrl('orders_home'));
    }

    /**
     * @JMS\Secure(roles="ROLE_ORDER_APPROVE, ROLE_ORDER_CANCEL, ROLE_ORDER_EDIT")
     * @Route("/order/cancel/{id}", name="order_cancel", options={"expose"=true})
     * @param Order $order
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function orderCancelAction(Order $order)
    {
        if (!$this->isOrderValidState($order)) {
            return $this->redirectOnInvalidOrderState($order);
        }

        $this->orderRepository()->cancelOrder($order);

        $this->dispatchApproveProcessEvent('order.canceled', $order);

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
            $this->getDoctrine()->getRepository('RbsSalesBundle:Stock')->addStockToOnHold($order, $order->getAgent()->getDepo());
        }

        $order->setOrderState(Order::ORDER_STATE_HOLD);
        $this->getDoctrine()->getRepository('RbsSalesBundle:Order')->update($order);

        $this->dispatchApproveProcessEvent('order.hold', $order);

        $this->flashMessage('success', 'Order Hold Successfully!');

        return $this->redirect($this->generateUrl('orders_home'));
    }

    /**
     * @JMS\Secure(roles="ROLE_ORDER_VIEW, ROLE_ORDER_CREATE, ROLE_ORDER_EDIT, ROLE_ORDER_APPROVE, ROLE_ORDER_CANCEL")
     * @Route("/order/summery/view/{id}", name="order_summery_view", options={"expose"=true})
     * @param Order $order
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function summeryViewAction(Order $order)
    {
        $stockRepo = $this->getDoctrine()->getRepository('RbsSalesBundle:Stock');
        /** @var OrderItem $item */
        foreach ($order->getOrderItems() as $item) {
            $stockItem = $stockRepo->findOneBy(
                array('item' => $item->getItem()->getId(), 'depo' => $order->getAgent()->getDepo()->getId())
            );
            $item->isAvailable = $stockItem->isStockAvailable($item->getQuantity());
        }

        return $this->render('RbsSalesBundle:Order:summeryView.html.twig', array(
            'order' => $order,
        ));
    }

    protected function isOrderValidState(Order $order)
    {
        if (in_array($order->getOrderState(), array(
            Order::ORDER_STATE_CANCEL,
            Order::ORDER_STATE_COMPLETE,
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
     * @Route("/order/{id}/payment-review", name="review_payment")
     * @param Order $order
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function paymentReviewAction(Order $order)
    {
        $agent = $order->getAgent();
        $currentCreditLimit = $this->agentRepository()->getCurrentCreditLimit($agent);
        $isOverCredit = $currentCreditLimit < $order->getTotalAmount();

        return $this->render('RbsSalesBundle:Order:paymentReview.html.twig', array(
            'order' => $order,
            'isOverCredit' => $isOverCredit,
            'currentCreditLimit' => $currentCreditLimit,
        ));
    }

    /**
     * @JMS\Secure(roles="ROLE_ORDER_VERIFY")
     * @Route("/order/{id}/order-review", name="order_review")
     * @param Order $order
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function orderReviewAction(Order $order)
    {
        $auditLogs = $this->getDoctrine()->getRepository('RbsCoreBundle:AuditLog')->getByTypeOrObjectId(array('order.approved', 'order.hold', 'payment.approved', 'payment.over.credit.approved'), $order->getId());

        return $this->render('RbsSalesBundle:Order:orderVerify.html.twig', array(
            'order' => $order,
            'auditLogs' => $auditLogs
        ));
    }

    /**
     * @JMS\Secure(roles="ROLE_PAYMENT_APPROVE")
     * @Route("/order/{id}/approve-payment", name="approve_payment")
     * @param Order $order
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function paymentApproveAction(Order $order)
    {
        $agent = $order->getAgent();
        $currentCreditLimit = $this->agentRepository()->getCurrentCreditLimit($agent);
        $isOverCredit = $currentCreditLimit < $order->getTotalAmount();

        if (!$agent->isVIP() && $isOverCredit) {
            $order->setPaymentState(Order::PAYMENT_STATE_CREDIT_APPROVAL);
        } else if ($order->getTotalAmount() === $order->getPaidAmount()) {
            $order->setPaymentState(Order::PAYMENT_STATE_PAID);
            $this->orderRepository()->adjustPaymentViaSms($order->getPayments());
        } else {
            $order->setPaymentState(Order::PAYMENT_STATE_PARTIALLY_PAID);
            $this->orderRepository()->adjustPaymentViaSms($order->getPayments());
        }

        $this->getDoctrine()->getRepository('RbsSalesBundle:Order')->update($order);

        $this->dispatchApproveProcessEvent('payment.approved', $order);

        $this->flashMessage('success', 'Payment Approved Successfully!');

        return $this->redirect($this->generateUrl('orders_home'));
    }

    /**
     * @JMS\Secure(roles="ROLE_PAYMENT_OVER_CREDIT_APPROVE")
     * @Route("/order/{id}/approve-credit-payment", name="approve_credit_payment")
     * @param Order $order
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function paymentOverCreditApproveAction(Order $order)
    {
        /** TODO: Refactor adjustment method. Multiple Update Query Executed */
        $this->orderRepository()->adjustPaymentViaSms($order->getPayments());

        if ($order->getTotalAmount() === $order->getPaidAmount()) {
            $order->setPaymentState(Order::PAYMENT_STATE_PAID);
        } else {
            $order->setPaymentState(Order::PAYMENT_STATE_PARTIALLY_PAID);
        }
        $this->getDoctrine()->getRepository('RbsSalesBundle:Order')->update($order);

        $this->dispatchApproveProcessEvent('payment.over.credit.approved', $order);

        $this->flashMessage('success', 'Payment Approved Successfully!');

        return $this->redirect($this->generateUrl('orders_home'));
    }

    /**
     * @JMS\Secure(roles="ROLE_ORDER_VERIFY")
     * @Route("/order/{id}/verify", name="order_verify")
     * @param Order $order
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function orderVerifyAction(Order $order)
    {
        if ($order->getOrderState() == Order::ORDER_STATE_PROCESSING &&
            in_array($order->getPaymentState(), array(Order::PAYMENT_STATE_PAID, Order::PAYMENT_STATE_PARTIALLY_PAID))
        ) {
            $order->setDeliveryState(Order::DELIVERY_STATE_READY);
            $this->getDoctrine()->getRepository('RbsSalesBundle:Order')->update($order);

            $this->dispatchApproveProcessEvent('order.verified', $order);

            $this->flashMessage('success', 'Order Verified Successfully and Ready for Delivery');
        }

        return $this->redirect($this->generateUrl('orders_home'));
    }
}