<?php

namespace Rbs\Bundle\SalesBundle\Controller;

use Doctrine\ORM\QueryBuilder;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use JMS\SecurityExtraBundle\Annotation as JMS;

/**
 * Sms Controller.
 *
 */
class SmsController extends Controller
{
    /**
     * @Route("/sms", name="sms_home")
     * @Template()
     * @JMS\Secure(roles="ROLE_ORDER_VIEW")
     */
    public function indexAction()
    {
        $datatable = $this->get('rbs_erp.sales.datatable.sms');
        $datatable->buildDatatable();

        return $this->render('RbsSalesBundle:Sms:index.html.twig', array(
            'datatable' => $datatable
        ));
    }

    /**
     * Lists all Sms entities.
     *
     * @Route("/sms_list_ajax", name="sms_list_ajax", options={"expose"=true})
     * @Method("GET")
     * @JMS\Secure(roles="ROLE_ORDER_VIEW")
     */
    public function listAjaxAction()
    {
        $datatable = $this->get('rbs_erp.sales.datatable.sms');
        $datatable->buildDatatable();

        $query = $this->get('sg_datatables.query')->getQueryFrom($datatable);
        /** @var QueryBuilder $qb */
        $function = function($qb)
        {
            $qb->andWhere("sms.status = 'UNREAD'");
        };
        $query->addWhereAll($function);
        return $query->getResponse();
    }

    /**
     * @Route("/sms/readable", name="sms_readable")
     * @Template()
     * @JMS\Secure(roles="ROLE_ORDER_VIEW")
     */
    public function indexReadableAction()
    {
        $datatable = $this->get('rbs_erp.sales.datatable.sms');
        $datatable->buildDatatable();

        return $this->render('RbsSalesBundle:Sms:readable.html.twig', array(
            'datatable' => $datatable
        ));
    }
    
    /**
     * Lists all Sms entities.
     *
     * @Route("/readable_sms_list_ajax", name="readable_sms_list_ajax", options={"expose"=true})
     * @Method("GET")
     * @JMS\Secure(roles="ROLE_ORDER_VIEW")
     */
    public function readableSmsListAjaxAction()
    {
        $datatable = $this->get('rbs_erp.sales.datatable.sms');
        $datatable->buildDatatable();

        $query = $this->get('sg_datatables.query')->getQueryFrom($datatable);
        /** @var QueryBuilder $qb */
        $function = function($qb)
        {
            $qb->andWhere("sms.status = 'READ'");
        };
        $query->addWhereAll($function);
        return $query->getResponse();
    }
}