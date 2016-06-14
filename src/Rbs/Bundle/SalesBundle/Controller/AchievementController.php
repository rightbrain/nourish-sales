<?php

namespace Rbs\Bundle\SalesBundle\Controller;

use Doctrine\ORM\QueryBuilder;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use JMS\SecurityExtraBundle\Annotation as JMS;

/**
 * Achievement Controller.
 *
 */
class AchievementController extends Controller
{
    /**
     * @Route("/achievement/list", name="achievement_list", options={"expose"=true})
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listAction(Request $request)
    {
        $datatable = $this->get('rbs_erp.sales.datatable.achievement');
        $datatable->buildDatatable();

        return $this->render('RbsSalesBundle:Achievement:index.html.twig', array(
            'datatable' => $datatable
        ));
    }

    /**
     * Lists all Achievement entities.
     *
     * @Route("/achievement_list_ajax", name="achievement_list_ajax", options={"expose"=true})
     * @Method("GET")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listAjaxAction(Request $request)
    {
        $datatable = $this->get('rbs_erp.sales.datatable.achievement');
        $datatable->buildDatatable();

        $query = $this->get('sg_datatables.query')->getQueryFrom($datatable);
        /** @var QueryBuilder $qb
         * @param $qb
         */
        $function = function($qb)
        {

        };
        $query->addWhereAll($function);

        return $query->getResponse();
    }
}