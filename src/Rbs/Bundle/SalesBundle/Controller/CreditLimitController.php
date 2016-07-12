<?php

namespace Rbs\Bundle\SalesBundle\Controller;

use DateTime;
use Doctrine\ORM\QueryBuilder;
use Rbs\Bundle\SalesBundle\Entity\CreditLimit;
use Rbs\Bundle\SalesBundle\Form\Type\CreditLimitForm;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use JMS\SecurityExtraBundle\Annotation as JMS;

/**
 * Credit Limit Controller.
 *
 */
class CreditLimitController extends BaseController
{
    /**
     * @Route("/credit/limit/list", name="credit_limit_list", options={"expose"=true})
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function cashDepositListAction(Request $request)
    {
        $datatable = $this->get('rbs_erp.sales.datatable.credit.limit');
        $datatable->buildDatatable();

        return $this->render('RbsSalesBundle:CreditLimit:index.html.twig', array(
            'datatable' => $datatable
        ));
    }

    /**
     * Lists all CreditLimit entities.
     *
     * @Route("/credit_limit_list_ajax", name="credit_limit_list_ajax", options={"expose"=true})
     * @Method("GET")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listAjaxAction(Request $request)
    {
        $datatable = $this->get('rbs_erp.sales.datatable.credit.limit');
        $datatable->buildDatatable();

        $query = $this->get('sg_datatables.query')->getQueryFrom($datatable);
        /** @var QueryBuilder $qb
         * @param $qb
         */
        $function = function($qb)
        {
            $qb->orderBy('credit_limits.createdAt', 'desc');
        };
        $query->addWhereAll($function);
        return $query->getResponse();
    }

    /**
     * @Route("/credit/limit/create", name="credit_limit_create")
     * @Template("RbsSalesBundle:CreditLimit:new.html.twig")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function createAction(Request $request)
    {
        $creditLimit = new CreditLimit();

        $form = $this->createForm(new CreditLimitForm(), $creditLimit);

        if ('POST' === $request->getMethod()) {
            $form->handleRequest($request);

            if ($form->isValid()) {

                $creditLimit->setCreatedAt(new \DateTime());
                $creditLimit->setCreatedBy($this->getUser());

                $this->getDoctrine()->getRepository('RbsSalesBundle:CreditLimit')->create($creditLimit);

                $this->get('session')->getFlashBag()->add(
                    'success',
                    'Credit Limit Add Successfully!'
                );

                return $this->redirect($this->generateUrl('credit_limit_list'));
            }
        }

        return array(
            'form' => $form->createView()
        );
    }
}