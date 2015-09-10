<?php

namespace Rbs\Bundle\SalesBundle\Controller;

use Doctrine\ORM\QueryBuilder;
use Rbs\Bundle\SalesBundle\Entity\Order;
use Rbs\Bundle\SalesBundle\Entity\OrderItem;
use Rbs\Bundle\SalesBundle\Form\Type\OrderForm;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * User Controller.
 *
 */
class OrderController extends Controller
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
        /** @var QueryBuilder $qb */
        $function = function($qb)
        {
//            $qb->join("stocks.item", 'i');
//            $qb->andWhere("stocks.deletedAt IS NULL");
        };
        $query->addWhereAll($function);
        return $query->getResponse();
    }

    /**
     * @Route("/order-create", name="order_create", options={"expose"=true})
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

                /** @var OrderItem $orderItems */
                foreach ($order->getOrderItems() as $orderItems) {
                    $orderItems->setOrder($order);
                    $orderItems->calculateTotalAmount(true);
                }
                $order->setTotalAmount($order->getItemsTotalAmount());

                $this->getDoctrine()->getRepository('RbsSalesBundle:Order')->create($order);

                $this->get('session')->getFlashBag()->add(
                    'success',
                    'Order Add Successfully!'
                );

                return $this->redirect($this->generateUrl('orders_home'));
            }
        }

        return array(
            'form' => $form->createView(),
        );
    }

    /**
     * @Route("/order-update/{id}", name="order_update", options={"expose"=true})
     * @Template("RbsSalesBundle:Order:edit.html.twig")
     * @param Request $request
     * @param Order $order
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function updateAction(Request $request, Order $order)
    {
        $form = $this->createForm(new OrderForm(), $order);

        if ('POST' === $request->getMethod()) {
            $oldItems = $order->getOrderItems();
            $form->handleRequest($request);

            if ($form->isValid()) {

                /** @var OrderItem $orderItems */
                foreach ($order->getOrderItems() as $orderItems) {
                    $orderItems->setOrder($order);
                    $orderItems->calculateTotalAmount(true);
                }
                $order->setTotalAmount($order->getItemsTotalAmount());

                $this->getDoctrine()->getRepository('RbsSalesBundle:Order')->create($order);

                $this->get('session')->getFlashBag()->add(
                    'success',
                    'Order Update Successfully!'
                );

                return $this->redirect($this->generateUrl('orders_home'));
            }
        }

        return array(
            'form' => $form->createView(),
            'order' => $order
        );
    }

    /**
     * @Route("/order-details/{id}", name="order_details", options={"expose"=true})
     * @param Order $order
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function detailsAction(Order $order)
    {
        return $this->render('RbsSalesBundle:Order:details.html.twig', array(
            'order' => $order
        ));
    }
}