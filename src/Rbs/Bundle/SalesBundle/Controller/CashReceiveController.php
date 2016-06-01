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
            $qb->join('cash_receives.depo', 'd');
            $qb->join('d.users', 'u');
            $qb->andWhere('u.id =:user');
            $qb->setParameter('user', $this->getUser()->getId());
            $qb->orderBy('cash_receives.receivedAt', 'desc');
        };
        $query->addWhereAll($function);
        return $query->getResponse();
    }

    /**
     * @Route("/cash/receive/create", name="cash_receive_create", options={"expose"=true})
     * @Template("RbsSalesBundle:CashReceive:form.html.twig")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function createAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $cashReceive = new CashReceive();
        $form = $this->createForm(new CashReceiveForm(), $cashReceive, array(
            'action' => $this->generateUrl('cash_receive_create'), 'method' => 'POST',
            'attr' => array('novalidate' => 'novalidate')
        ));
        $getDepoId = $em->getRepository('RbsCoreBundle:Depo')->getDepoId($this->getUser()->getId());
        $cashReceivedId = $em->getRepository('RbsSalesBundle:CashReceive')->getLastCashReceivedId($this->getUser()->getId());
        if($cashReceivedId!=null){
            $lastTotalReceivedAmount = $em->getRepository('RbsSalesBundle:CashReceive')->lastTotalReceivedAmount($cashReceivedId);
            $lastTotalAmount = $lastTotalReceivedAmount!=null?$lastTotalReceivedAmount:0;
            $cashReceive->setTotalReceivedAmount($lastTotalAmount+$request->request->all()['cash_receive']['amount']);
        }else{
            $cashReceive->setTotalReceivedAmount(0);
        }

        if ('POST' === $request->getMethod()) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $cashReceive->setReceivedAt(new \DateTime());
                $cashReceive->setReceivedBy($this->getUser());
                $cashReceive->setDepo($em->getRepository('RbsCoreBundle:Depo')->find($getDepoId[0]['id']));
                $em->getRepository('RbsSalesBundle:CashReceive')->create($cashReceive);
                $this->flashMessage('success', 'Cash Receive Successfully!');
                return $this->redirect($this->generateUrl('cash_receive_list'));
            }
        }
        
        return array(
            'form' => $form->createView()
        );
    }
}