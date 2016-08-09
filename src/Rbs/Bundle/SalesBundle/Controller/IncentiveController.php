<?php

namespace Rbs\Bundle\SalesBundle\Controller;

use Doctrine\ORM\QueryBuilder;
use Rbs\Bundle\SalesBundle\Entity\Incentive;
use Rbs\Bundle\SalesBundle\Entity\Payment;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use JMS\SecurityExtraBundle\Annotation as JMS;

/**
 * Incentive Controller.
 *
 */
class IncentiveController extends BaseController
{
    /**
     * @Route("/incentives", name="incentives_home")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @JMS\Secure(roles="ROLE_ADMIN")
     */
    public function indexAction(Request $request)
    {
        $datatable = $this->get('rbs_erp.sales.datatable.incentive');
        $datatable->buildDatatable();

        return $this->render('RbsSalesBundle:Incentive:index.html.twig', array(
            'datatable' => $datatable,
        ));
    }
    
    /**
     * Lists all Incentive entities.
     *
     * @Route("/incentives_list_ajax", name="incentives_list_ajax", options={"expose"=true})
     * @Method("GET")
     * @JMS\Secure(roles="ROLE_ADMIN")
     */
    public function listAjaxAction()
    {
        $datatable = $this->get('rbs_erp.sales.datatable.incentive');
        $datatable->buildDatatable();

        $query = $this->get('sg_datatables.query')->getQueryFrom($datatable);
        /** @var QueryBuilder $qb */
        $function = function($qb)
        {
            $qb->join('incentives.agent', 'a');
            $qb->join('a.user', 'u');
            $qb->join('u.profile', 'p');
        };
        $query->addWhereAll($function);

        return $query->getResponse();
    }

    /**
     * @Route("/incentive/details/{id}", name="incentive_details", options={"expose"=true})
     * @param Incentive $incentive
     * @return \Symfony\Component\HttpFoundation\Response
     * @JMS\Secure(roles="ROLE_ADMIN")
     */
    public function detailsAction(Incentive $incentive)
    {
        return $this->render('RbsSalesBundle:Incentive:details.html.twig', array(
            'incentive' => $incentive,
        ));
    }

    /**
     * @Route("/incentive/approve/{id}", name="incentive_approve", options={"expose"=true})
     * @param Incentive $incentive
     * @return \Symfony\Component\HttpFoundation\Response
     * @JMS\Secure(roles="ROLE_ADMIN")
     */
    public function orderApproveAction(Incentive $incentive)
    {
        $em = $this->getDoctrine()->getManager();
        $payment = new Payment();
        $payment->setAgent($incentive->getAgent());
        $payment->setAmount($incentive->getAmount());
        $payment->setPaymentMethod(Payment::PAYMENT_METHOD_INCENTIVE);
        $payment->setRemark('Refund for incentive.');
        $payment->setDepositDate(new \DateTime());
        $payment->setTransactionType(Payment::CR);

        $incentive->setApprovedAt(new \DateTime());
        $incentive->setApprovedBy($this->getUser());
        $incentive->setStatus(Incentive::APPROVED);

        $this->getDoctrine()->getRepository('RbsSalesBundle:Incentive')->update($incentive);

        $em->getRepository('RbsSalesBundle:Order')->orderAmountAdjust($payment);
        $em->getRepository('RbsSalesBundle:Payment')->create($payment);

        $this->flashMessage('success', 'Incentive Approve Successfully!');

        return $this->redirect($this->generateUrl('incentives_home'));
    }

    /**
     * @Route("/incentive/cancel/{id}", name="incentive_cancel", options={"expose"=true})
     * @param Incentive $incentive
     * @return \Symfony\Component\HttpFoundation\Response
     * @JMS\Secure(roles="ROLE_ADMIN")
     */
    public function orderCancelAction(Incentive $incentive)
    {
        $incentive->setApprovedAt(new \DateTime());
        $incentive->setApprovedBy($this->getUser());
        $incentive->setStatus(Incentive::DENIED);

        $this->getDoctrine()->getRepository('RbsSalesBundle:Incentive')->update($incentive);

        $this->flashMessage('success', 'Incentive Cancel Successfully!');

        return $this->redirect($this->generateUrl('incentives_home'));
    }
}