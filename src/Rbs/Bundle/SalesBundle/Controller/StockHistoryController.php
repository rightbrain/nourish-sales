<?php

namespace Rbs\Bundle\SalesBundle\Controller;

use Doctrine\ORM\QueryBuilder;
use Rbs\Bundle\SalesBundle\Entity\StockHistory;
use Rbs\Bundle\SalesBundle\Form\Type\StockHistoryForm;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * Stock History Controller.
 *
 */
class StockHistoryController extends Controller
{
    /**
     * @Route("/stock/history/list/{stock}", name="stock_history_list", options={"expose"=true})
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function stockHistoryAllAction(Request $request)
    {
        $stock = $request->attributes->get('stock');
        $stockHistories = $this->getDoctrine()->getRepository('RbsSalesBundle:StockHistory')->findBy(array(
            'stock' => $stock
        ));

        $datatable = $this->get('rbs_erp.sales.datatable.stock.history');
        $datatable->buildDatatable();

        return $this->render('RbsSalesBundle:Stock:historyList.html.twig', array(
            'stockHistories' => $stockHistories,
            'datatable' => $datatable
        ));
    }

    /**
     * Lists all Category entities.
     *
     * @Route("/stock_history_list_ajax/{stock}", name="stock_history_list_ajax", options={"expose"=true})
     * @Method("GET")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listAjaxAction(Request $request)
    {
        $stock = $request->attributes->get('stock');
        $datatable = $this->get('rbs_erp.sales.datatable.stock.history');
        $datatable->buildDatatable();

        $query = $this->get('sg_datatables.query')->getQueryFrom($datatable);
        /** @var QueryBuilder $qb
         * @param $qb
         */
        $function = function($qb) use ($stock)
        {
            $qb->andWhere("stock_histories.stock = :stock");
            $qb->setParameter("stock", $stock);
        };
        $query->addWhereAll($function);
        return $query->getResponse();
    }
}