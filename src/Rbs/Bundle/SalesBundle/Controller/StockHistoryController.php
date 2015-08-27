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
 * User Controller.
 *
 */
class StockHistoryController extends Controller
{
//    /**
//     * @Route("/stock-history", name="stock_history_home")
//     * @Method("GET")
//     * @Template()
//     */
//    public function indexAction()
//    {
//        $datatable = $this->get('rbs_erp.sales.datatable.stock.history');
//        $datatable->buildDatatable();
//
//        return $this->render('RbsSalesBundle:StockHistory:index.html.twig', array(
//            'datatable' => $datatable
//        ));
//    }

    /**
     * Lists all Category entities.
     *
     * @Route("/stock_history_list_ajax", name="stock_history_list_ajax", options={"expose"=true})
     * @Method("GET")
     */
    public function listAjaxAction()
    {
        $datatable = $this->get('rbs_erp.sales.datatable.stock.history');
        $datatable->buildDatatable();

        $query = $this->get('sg_datatables.query')->getQueryFrom($datatable);
        /** @var QueryBuilder $qb */
        $function = function($qb)
        {
//            $qb->join("stock_histories.stock", 'stock');
//            $qb->join("stock.item", 'item');
//            $qb->andWhere("stock_histories.deletedAt IS NULL");
        };
        $query->addWhereAll($function);
        return $query->getResponse();
    }

    /**
     * @Route("/stock-history-create", name="stock_history_create")
     * @Template("RbsSalesBundle:StockHistory:new.html.twig")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function createAction(Request $request)
    {
        $stockHistory = new StockHistory();

        $form = $this->createForm(new StockHistoryForm(), $stockHistory);

        if ('POST' === $request->getMethod()) {
            $form->handleRequest($request);

            if ($form->isValid()) {

                $quantity = $form->getData()->getQuantity();
                $stock = $this->getDoctrine()->getRepository('RbsSalesBundle:Stock')->find($stockHistory->getStock()->getId());
                $quantity = $stock->getOnHand() + $quantity;
                $stock->setOnHand($quantity);

                $this->getDoctrine()->getRepository('RbsSalesBundle:Stock')->update($stock);

                $this->getDoctrine()->getRepository('RbsSalesBundle:StockHistory')->create($stockHistory);

                $this->checkAvailableOnDemand($stock);

                $this->get('session')->getFlashBag()->add(
                    'success',
                    'Stock Item Quantity Add Successfully!'
                );

                return $this->redirect($this->generateUrl('stocks_home'));
            }
        }

        return array(
            'form' => $form->createView()
        );
    }

    /**
     * @param $stock
     */
    protected function checkAvailableOnDemand($stock)
    {
        if ($stock->getOnHand() < $stock->getOnHold()) {
            $stock->setAvailableOnDemand(0);
            $this->getDoctrine()->getRepository('RbsSalesBundle:Stock')->update($stock);
        } elseif ($stock->getOnHand() >= $stock->getOnHold()) {
            $stock->setAvailableOnDemand(1);
            $this->getDoctrine()->getRepository('RbsSalesBundle:Stock')->update($stock);
        }
    }
}