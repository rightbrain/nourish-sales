<?php

namespace Rbs\Bundle\SalesBundle\Controller;

use Doctrine\DBAL\Query\QueryBuilder;
use JMS\SecurityExtraBundle\Annotation as JMS;
use Rbs\Bundle\SalesBundle\Entity\Delivery;
use Rbs\Bundle\SalesBundle\Entity\Order;
use Rbs\Bundle\SalesBundle\Event\OrderApproveEvent;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

/**
 * User Controller.
 *
 */
class DeliveryController extends BaseController
{
    /**
     * @Route("/deliveries", name="deliveries_home")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @JMS\Secure(roles="ROLE_ORDER_VIEW, ROLE_ORDER_CREATE, ROLE_ORDER_EDIT, ROLE_ORDER_APPROVE, ROLE_ORDER_CANCEL")
     */
    public function indexAction(Request $request)
    {
        $datatable = $this->get('rbs_erp.sales.datatable.delivery');
        $datatable->buildDatatable();

        return $this->render('RbsSalesBundle:Delivery:index.html.twig', array(
            'datatable' => $datatable,
        ));
    }

    /**
     * Lists all Category entities.
     *
     * @Route("/delivery_list_ajax", name="delivery_list_ajax", options={"expose"=true})
     * @Method("GET")
     * @JMS\Secure(roles="ROLE_ORDER_VIEW, ROLE_ORDER_CREATE, ROLE_ORDER_EDIT, ROLE_ORDER_APPROVE, ROLE_ORDER_CANCEL")
     */
    public function listAjaxAction(Request $request)
    {
        $datatable = $this->get('rbs_erp.sales.datatable.delivery');
        $datatable->buildDatatable();

        $dateFilter = $request->query->get('columns[1][search][value]', null, true);

        // Reset Date Column search's value to Skip DataTable native search functionality for Date Column
        $columns = $request->query->get('columns');
        $columns[1]['search']['value'] = '';
        $request->query->set('columns', $columns);

        $query = $this->get('sg_datatables.query')->getQueryFrom($datatable);

        /** @var QueryBuilder $qb */
        $function = function($qb) use ($dateFilter)
        {
            $qb->andWhere('orderRef.deliveryState IN (:deliveryState)')
                ->setParameter('deliveryState', array(Order::DELIVERY_STATE_READY, Order::DELIVERY_STATE_PARTIALLY_SHIPPED));
            if ($dateFilter) {
                $qb->andWhere('orderRef.createdAt BETWEEN :fromDate AND :toDate')
                    ->setParameter('fromDate', date('Y-m-d 00:00:00', strtotime($dateFilter)))
                    ->setParameter('toDate', date('Y-m-d 23:59:59', strtotime($dateFilter)));
            }
        };
        $query->addWhereAll($function);

        return $query->getResponse();
    }

    /**
     * @JMS\Secure(roles="ROLE_PAYMENT_APPROVE")
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

            $this->deliveryRepository()->prepareDeliveryOnVerifyOrder($order);

            $this->dispatchApproveProcessEvent('order.verified', $order);

            $this->flashMessage('success', 'Order Verified Successfully and Ready for Delivery');
        }

        return $this->redirect($this->generateUrl('orders_home'));
    }

    /**
     * @JMS\Secure(roles="ROLE_USER")
     * @Route("/delivery/{id}", name="delivery_view", options={"expose"=true})
     * @param Delivery $delivery
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function view(Delivery $delivery)
    {
        return $this->render('RbsSalesBundle:Delivery:view.html.twig', array(
            'delivery'  => $delivery,
            'order'     => $delivery->getOrderRef(),
            'customer'  => $delivery->getOrderRef()->getCustomer()
        ));
    }
}