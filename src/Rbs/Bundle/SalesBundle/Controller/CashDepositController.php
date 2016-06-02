<?php

namespace Rbs\Bundle\SalesBundle\Controller;

use Doctrine\ORM\QueryBuilder;
use Rbs\Bundle\SalesBundle\Entity\CashDeposit;
use Rbs\Bundle\SalesBundle\Form\Type\CashDepositForm;
use Rbs\Bundle\UserBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use JMS\SecurityExtraBundle\Annotation as JMS;

/**
 * Cash Deposit Controller.
 *
 */
class CashDepositController extends BaseController
{
    /**
     * @Route("/cash/deposit/list", name="cash_deposit_list", options={"expose"=true})
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function stockHistoryAllAction(Request $request)
    {
        $datatable = $this->get('rbs_erp.sales.datatable.cash.deposit');
        $datatable->buildDatatable();

        return $this->render('RbsSalesBundle:CashDeposit:index.html.twig', array(
            'datatable' => $datatable
        ));
    }

    /**
     * Lists all CashDeposit entities.
     *
     * @Route("/cash_deposit_list_ajax", name="cash_deposit_list_ajax", options={"expose"=true})
     * @Method("GET")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listAjaxAction(Request $request)
    {
        $datatable = $this->get('rbs_erp.sales.datatable.cash.deposit');
        $datatable->buildDatatable();

        $query = $this->get('sg_datatables.query')->getQueryFrom($datatable);
        /** @var QueryBuilder $qb
         * @param $qb
         */
        $function = function($qb)
        {
            $qb->join('cash_deposits.depo', 'd');
            $qb->join('d.users', 'u');
            $qb->andWhere('u.id =:user');
            $qb->setParameter('user', $this->getUser()->getId());
            $qb->orderBy('cash_deposits.depositedAt', 'desc');
        };
        $query->addWhereAll($function);
        return $query->getResponse();
    }

    /**
     * @Route("/cash/deposit/create", name="cash_deposit_create", options={"expose"=true})
     * @Template("RbsSalesBundle:CashDeposit:form.html.twig")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function createAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $cashDeposit = new CashDeposit();
        $form = $this->createForm(new CashDepositForm(), $cashDeposit, array(
            'action' => $this->generateUrl('cash_deposit_create'), 'method' => 'POST',
            'attr' => array('novalidate' => 'novalidate')
        ));

        $getDepoId = $em->getRepository('RbsCoreBundle:Depo')->getDepoId($this->getUser()->getId());
        $cashDepositedId = $em->getRepository('RbsSalesBundle:CashDeposit')->getLastCashDepositId($this->getUser()->getId());
        if($cashDepositedId!=null && 'POST' === $request->getMethod()){
            $lastTotalDepositedAmount = $em->getRepository('RbsSalesBundle:CashDeposit')->lastTotalDepositAmount($cashDepositedId[0]['id']);
            $lastTotalAmount = $lastTotalDepositedAmount!=null?$lastTotalDepositedAmount[0]:0;
            $total = $lastTotalAmount['totalDepositedAmount']+(float) $_POST['cash_deposit']['deposit'];
            $cashDeposit->setTotalDepositedAmount($total);
        }else{
            $cashDeposit->setTotalDepositedAmount(0);
        }
        if ('POST' === $request->getMethod()) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $cashDeposit->setDepositedBy($this->getUser());
                $cashDeposit->setDepo($em->getRepository('RbsCoreBundle:Depo')->find($getDepoId[0]['id']));
                $this->getDoctrine()->getManager()->getRepository('RbsSalesBundle:CashDeposit')->create($cashDeposit);
                $this->flashMessage('success', 'Cash Deposit Successfully!');
                return $this->redirect($this->generateUrl('cash_deposit_list'));
            }
        }
        return array(
            'form' => $form->createView()
        );
    }
}