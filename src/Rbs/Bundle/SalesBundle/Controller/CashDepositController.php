<?php

namespace Rbs\Bundle\SalesBundle\Controller;

use Doctrine\ORM\QueryBuilder;
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
     * Lists all Target entities.
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
}