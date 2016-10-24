<?php

namespace Rbs\Bundle\SalesBundle\Controller;

use Doctrine\ORM\QueryBuilder;
use Rbs\Bundle\SalesBundle\Entity\Agent;
use Rbs\Bundle\SalesBundle\Entity\Payment;
use Rbs\Bundle\SalesBundle\Form\Search\Type\AgentSearchType;
use Rbs\Bundle\SalesBundle\Form\Search\Type\TwoDateSearchType;
use Rbs\Bundle\SalesBundle\Form\Type\PaymentForm;
use Rbs\Bundle\UserBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use JMS\SecurityExtraBundle\Annotation as JMS;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Payment Controller.
 *
 */
class PaymentController extends BaseController
{
    /**
     * @Route("/payments", name="payments_home", options={"expose"=true})
     * @Template()
     * @JMS\Secure(roles="ROLE_HEAD_OFFICE_USER, ROLE_PAYMENT_VIEW, ROLE_PAYMENT_CREATE, ROLE_PAYMENT_APPROVE, ROLE_PAYMENT_OVER_CREDIT_APPROVE")
     */
    public function indexAction()
    {
        $datatable = $this->get('rbs_erp.sales.datatable.payment');
        $datatable->buildDatatable();

        return $this->render('RbsSalesBundle:Payment:index.html.twig', array(
            'datatable' => $datatable,
        ));
    }

    /**
     * @Route("/payment_list_ajax", name="payment_list_ajax", options={"expose"=true})
     * @Method("GET")
     * @JMS\Secure(roles="ROLE_HEAD_OFFICE_USER, ROLE_PAYMENT_VIEW, ROLE_PAYMENT_CREATE, ROLE_PAYMENT_APPROVE, ROLE_PAYMENT_OVER_CREDIT_APPROVE")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listAjaxAction(Request $request)
    {
        /** @var User $user */
        $user = $this->getUser();
        $agentRepository = $this->getDoctrine()->getRepository('RbsSalesBundle:Agent');
        $datatable = $this->get('rbs_erp.sales.datatable.payment');
        $datatable->buildDatatable();

        $dateFilter = $request->query->get('columns[0][search][value]', null, true);

        // Reset Date Column search's value to Skip DataTable native search functionality for Date Column
        $columns = $request->query->get('columns');
        $columns[0]['search']['value'] = '';
        $request->query->set('columns', $columns);

        $query = $this->get('sg_datatables.query')->getQueryFrom($datatable);
        /** @var QueryBuilder $qb */
        $function = function($qb) use ($dateFilter, $user, $agentRepository)
        {
            if ($dateFilter) {
                list($fromDate, $toDate) = explode('--', $dateFilter);

                if (!empty($fromDate) && !empty($toDate)) {
                    $qb->andWhere('sales_payments.createdAt BETWEEN :fromDate AND :toDate')
                        ->setParameter('fromDate', date('Y-m-d 00:00:00', strtotime($fromDate)))
                        ->setParameter('toDate', date('Y-m-d 23:59:59', strtotime($toDate)));
                } else if (!empty($fromDate) && empty($toDate)) {
                    $qb->andWhere('sales_payments.createdAt BETWEEN :fromDate AND :toDate')
                        ->setParameter('fromDate', date('Y-m-d 00:00:00', strtotime($fromDate)))
                        ->setParameter('toDate', date('Y-m-d 23:59:59', strtotime($fromDate)));
                }
            }
            if ($user->getUserType() == User::AGENT) {
                $agent = $agentRepository->findOneBy(array('user' => $user->getId()));
                $qb->andWhere('sales_payments.agent = :agent')->setParameter('agent', array($agent));
            }
        };
        $query->addWhereAll($function);

        return $query->getResponse();
    }

    /**
     * @Route("/payment/create", name="payment_create", options={"expose"=true})
     * @Template("RbsSalesBundle:Payment:new.html.twig")
     * @param Request $request
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @JMS\Secure(roles="ROLE_HEAD_OFFICE_USER, ROLE_PAYMENT_CREATE, ROLE_PAYMENT_APPROVE, ROLE_PAYMENT_OVER_CREDIT_APPROVE")
     */
    public function createAction(Request $request)
    {
        $payment = new Payment();
        $form = $this->createForm(new PaymentForm($this->get('request')), $payment, array(
            'action' => $this->generateUrl('payment_create'),
            'method' => 'POST',
            'attr' => array('novalidate' => 'novalidate')
        ));

        if ('POST' === $request->getMethod()) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $payment->setTransactionType(Payment::CR);
                $payment->setVerified(true);
                $em = $this->getDoctrine()->getManager();
                $em->getRepository('RbsSalesBundle:Order')->orderAmountAdjust($payment);
                $em->getRepository('RbsSalesBundle:Payment')->create($payment);

                $this->flashMessage('success', 'Payment Added Successfully');
                return $this->redirect($this->generateUrl('payments_home'));
            }
        }

        return array(
            'form' => $form->createView(),
        );
    }

    /**
     * @Route("/payment/partial_payment_orders/{id}", name="partial_payment_orders", options={"expose"=true})
     * @param Agent $agent
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @JMS\Secure(roles="ROLE_HEAD_OFFICE_USER, ROLE_PAYMENT_CREATE, ROLE_PAYMENT_APPROVE, ROLE_PAYMENT_OVER_CREDIT_APPROVE")
     */
    public function getAgentPartialOrder(Agent $agent)
    {
        $orders = $this->getDoctrine()->getRepository('RbsSalesBundle:Order')->getAgentWiseOrder($agent->getId());

        $orderArr = array();
        foreach ($orders as $order) {
            $orderArr[] = array('id' => $order->getId(), 'text' => $order->getOrderIdAndDueAmount());
        }

        return new JsonResponse($orderArr);
    }

    /**
     * @Route("/agents/ledger", name="agents_laser")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @JMS\Secure(roles="ROLE_HEAD_OFFICE_USER, ROLE_PAYMENT_CREATE, ROLE_PAYMENT_APPROVE, ROLE_PAYMENT_OVER_CREDIT_APPROVE")
     */
    public function laserAction(Request $request)
    {
        $form = new AgentSearchType();
        $data = $request->query->get($form->getName());
        $formSearch = $this->createForm($form, $data);

        if(!empty($data['start_date'])){
            $agentPreviousDebitLaserTotal = $this->getDoctrine()->getRepository('RbsSalesBundle:Payment')->getAgentPreviousDebitLaserTotal($data);
            $agentPreviousCreditLaserTotal = $this->getDoctrine()->getRepository('RbsSalesBundle:Payment')->getAgentPreviousCreditLaserTotal($data);
        }else{
            $agentPreviousDebitLaserTotal = 0;
            $agentPreviousCreditLaserTotal = 0;
        }
        $previousBalance = $agentPreviousCreditLaserTotal - $agentPreviousDebitLaserTotal;

        $agentDebitLaserTotal = $this->getDoctrine()->getRepository('RbsSalesBundle:Payment')->getAgentDebitLaserTotal($data);
        $agentCreditLaserTotal = $this->getDoctrine()->getRepository('RbsSalesBundle:Payment')->getAgentCreditLaserTotal($data);
        $currentBalance = $agentCreditLaserTotal - $agentDebitLaserTotal;

        $agentLaser = $this->getDoctrine()->getRepository('RbsSalesBundle:Payment')->getAgentLaser($data);
        if($data['agent'] != null){
            $agent = $this->getDoctrine()->getRepository('RbsSalesBundle:Agent')->find($data['agent']);
        }else{
            $agent = null;
        }

        return $this->render('RbsSalesBundle:Ledger:ledger.html.twig', array(
            'agentLaser' => $agentLaser,
            'formSearch' => $formSearch->createView(),
            'agentPreviousDebitLaserTotal' => $agentPreviousDebitLaserTotal,
            'agent' => $agent,
            'agentPreviousCreditLaserTotal' => $agentPreviousCreditLaserTotal,
            'previousBalance' => $previousBalance,
            'agentDebitLaserTotal' => $agentDebitLaserTotal,
            'agentCreditLaserTotal' => $agentCreditLaserTotal,
            'currentBalance' => $currentBalance,
        ));
    }

    /**
     * @Route("/my/ledger", name="my_laser")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @JMS\Secure(roles="ROLE_AGENT")
     */
    public function myLaserAction(Request $request)
    {
        $userId = $this->getUser()->getId();
        $agent = $this->getDoctrine()->getRepository('RbsSalesBundle:Agent')->findOneBy(array('user' => $userId));

        $form = new TwoDateSearchType();
        $data = $request->query->get($form->getName());
        $formSearch = $this->createForm($form, $data);

        if(!empty($data['start_date'])){
            $agentPreviousDebitLaserTotal = $this->getDoctrine()->getRepository('RbsSalesBundle:Payment')->getMyPreviousDebitLaserTotal($agent->getId(), $data);
            $agentPreviousCreditLaserTotal = $this->getDoctrine()->getRepository('RbsSalesBundle:Payment')->getMyPreviousCreditLaserTotal($agent->getId(), $data);
        }else{
            $agentPreviousDebitLaserTotal = 0;
            $agentPreviousCreditLaserTotal = 0;
        }
        $previousBalance = $agentPreviousCreditLaserTotal - $agentPreviousDebitLaserTotal;

        $agentDebitLaserTotal = $this->getDoctrine()->getRepository('RbsSalesBundle:Payment')->getMyDebitLaserTotal($agent->getId(), $data);
        $agentCreditLaserTotal = $this->getDoctrine()->getRepository('RbsSalesBundle:Payment')->getMyCreditLaserTotal($agent->getId(), $data);
        $currentBalance = $agentCreditLaserTotal - $agentDebitLaserTotal;

        $agentLaser = $this->getDoctrine()->getRepository('RbsSalesBundle:Payment')->getMyLaser($agent->getId(), $data);

        return $this->render('RbsSalesBundle:Ledger:my-ledger.html.twig', array(
            'agentLaser' => $agentLaser,
            'formSearch' => $formSearch->createView(),
            'agentPreviousDebitLaserTotal' => $agentPreviousDebitLaserTotal,
            'agent' => $agent,
            'agentPreviousCreditLaserTotal' => $agentPreviousCreditLaserTotal,
            'previousBalance' => $previousBalance,
            'agentDebitLaserTotal' => $agentDebitLaserTotal,
            'agentCreditLaserTotal' => $agentCreditLaserTotal,
            'currentBalance' => $currentBalance,
        ));
    }

    /**
     * @Route("/payment_amount_verified/{id}", name="payment_amount_verified", options={"expose"=true})
     * @param Payment $payment
     * @return Response
     */
    public function verifyAction(Payment $payment)
    {
        $payment->setVerified(true);
        $this->getDoctrine()->getRepository('RbsSalesBundle:Payment')->update($payment);

        return new Response(json_encode(array("message" => 'Payment Verified')), 200);
    }
}