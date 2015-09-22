<?php

namespace Rbs\Bundle\SalesBundle\Controller;

use Doctrine\ORM\QueryBuilder;
use Rbs\Bundle\SalesBundle\Entity\Customer;
use Rbs\Bundle\SalesBundle\Entity\Payment;
use Rbs\Bundle\SalesBundle\Form\Type\PaymentForm;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use JMS\SecurityExtraBundle\Annotation as JMS;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Payment Controller.
 *
 */
class PaymentController extends BaseController
{
    /**
     * @Route("/payments", name="payments_home")
     * @Template()
     * @JMS\Secure(roles="ROLE_ORDER_VIEW, ROLE_PAYMENT_CREATE, ROLE_PAYMENT_APPROVE, ROLE_PAYMENT_OVER_CREDIT_APPROVE")
     */
    public function indexAction()
    {
        $datatable = $this->get('rbs_erp.sales.datatable.payment');
        $datatable->buildDatatable();

        return $this->render('RbsSalesBundle:Payment:index.html.twig', array(
            'datatable' => $datatable
        ));
    }

    /**
     * Lists all Payment entities.
     *
     * @Route("/payment_list_ajax", name="payment_list_ajax", options={"expose"=true})
     * @Method("GET")
     * @JMS\Secure(roles="ROLE_PAYMENT_VIEW, ROLE_PAYMENT_CREATE, ROLE_PAYMENT_APPROVE, ROLE_PAYMENT_OVER_CREDIT_APPROVE")
     */
    public function listAjaxAction()
    {
        $datatable = $this->get('rbs_erp.sales.datatable.payment');
        $datatable->buildDatatable();

        $query = $this->get('sg_datatables.query')->getQueryFrom($datatable);
        /** @var QueryBuilder $qb */
        $function = function($qb)
        {

        };
        $query->addWhereAll($function);
        return $query->getResponse();
    }

    /**
     * @Route("/payment/create", name="payment_create", options={"expose"=true})
     * @Template("RbsSalesBundle:Payment:new.html.twig")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @JMS\Secure(roles="ROLE_PAYMENT_CREATE, ROLE_PAYMENT_APPROVE, ROLE_PAYMENT_OVER_CREDIT_APPROVE")
     */
    public function createAction(Request $request)
    {
        $payment = new Payment();
        $form = $this->createForm(new PaymentForm($this->get('request')), $payment, array(
            'action' => $this->generateUrl('payment_create'),
            'method' => 'POST',
        ));

        if ('POST' === $request->getMethod()) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->getRepository('RbsSalesBundle:Order')->orderAmountAdjust($payment);
                $em->getRepository('RbsSalesBundle:Payment')->create($payment);

                $this->flashMessage('success', 'Payment Add Successfully!');
                return $this->redirect($this->generateUrl('payments_home'));
            }
        }

        return array(
            'form' => $form->createView(),
        );
    }

    /**
     * @Route("/payment/partial_payment_orders/{id}", name="partial_payment_orders", options={"expose"=true})
     * @param Customer $customer
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @JMS\Secure(roles="ROLE_PAYMENT_CREATE, ROLE_PAYMENT_APPROVE, ROLE_PAYMENT_OVER_CREDIT_APPROVE")
     */
    public function getCustomerPartialOrder(Customer $customer)
    {
        $orders = $this->getDoctrine()->getRepository('RbsSalesBundle:Order')->getCustomerWiseOrder($customer->getId());

        $orderArr = array();
        foreach ($orders as $order) {
            $orderArr[] = array('id' => $order->getId(), 'text' => $order->getOrderIdAndDueAmount());
        }

        return new JsonResponse($orderArr);
    }
}