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
            $qb->join('targets.user', 'u');
            $qb->where('targets.quantity > 0');
            $qb->andWhere('targets.startDate is not null');
            $qb->andWhere('targets.endDate is not null');
            $qb->andWhere('u.userType = :RSM');
            $qb->setParameter('RSM', User::RSM);
            $qb->orderBy('targets.createdAt', 'desc');
        };
        $query->addWhereAll($function);
        return $query->getResponse();
    }

    /**
     * @Route("/cash/deposit/create", name="cash_deposit_create", options={"expose"=true})
     * @Template("RbsSalesBundle:CashDeposit:new.html.twig")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function createAction(Request $request)
    {
        $cashDeposit = new CashDeposit();
        $form = $this->createForm(new CashDepositForm(), $cashDeposit, array(
            'action' => $this->generateUrl('cash_deposit_create'), 'method' => 'POST',
            'attr' => array('novalidate' => 'novalidate')
        ));

        if ('POST' === $request->getMethod()) {
            $form->handleRequest($request);

            if ($form->isValid()) {
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