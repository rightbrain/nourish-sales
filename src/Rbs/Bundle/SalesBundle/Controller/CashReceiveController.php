<?php

namespace Rbs\Bundle\SalesBundle\Controller;

use Doctrine\ORM\QueryBuilder;
use Rbs\Bundle\SalesBundle\Entity\CashReceive;
use Rbs\Bundle\SalesBundle\Form\Type\CashReceiveForm;
use Rbs\Bundle\UserBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use JMS\SecurityExtraBundle\Annotation as JMS;

/**
 * Cash Receive Controller.
 *
 */
class CashReceiveController extends BaseController
{
    /**
     * @Route("/cash/receive/list", name="cash_receive_list", options={"expose"=true})
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function stockHistoryAllAction(Request $request)
    {
        $datatable = $this->get('rbs_erp.sales.datatable.cash.receive');
        $datatable->buildDatatable();

        return $this->render('RbsSalesBundle:CashReceive:index.html.twig', array(
            'datatable' => $datatable
        ));
    }

    /**
     * Lists all CashReceive entities.
     *
     * @Route("/cash_receive_list_ajax", name="cash_receive_list_ajax", options={"expose"=true})
     * @Method("GET")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listAjaxAction(Request $request)
    {
        $datatable = $this->get('rbs_erp.sales.datatable.cash.receive');
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
     * @Route("/cash/receive/create", name="cash_receive_create", options={"expose"=true})
     * @Template("RbsSalesBundle:CashReceive:new.html.twig")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function createAction(Request $request)
    {
        $cashReceive = new CashReceive();
        $form = $this->createForm(new CashReceiveForm(), $cashReceive, array(
            'action' => $this->generateUrl('cash_receive_create'), 'method' => 'POST',
            'attr' => array('novalidate' => 'novalidate')
        ));

        if ('POST' === $request->getMethod()) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $this->getDoctrine()->getManager()->getRepository('RbsSalesBundle:CashReceive')->create($cashReceive);
                $this->flashMessage('success', 'Cash Receive Successfully!');
                return $this->redirect($this->generateUrl('cash_receive_list'));
            }
        }
        return array(
            'form' => $form->createView()
        );
    }
}