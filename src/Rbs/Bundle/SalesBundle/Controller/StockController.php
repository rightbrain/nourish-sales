<?php

namespace Rbs\Bundle\SalesBundle\Controller;

use Doctrine\ORM\QueryBuilder;
use Rbs\Bundle\SalesBundle\Entity\Stock;
use Rbs\Bundle\SalesBundle\Entity\StockHistory;
use Rbs\Bundle\SalesBundle\Form\Type\StockHistoryForm;
use Rbs\Bundle\SalesBundle\RbsSalesBundle;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * User Controller.
 *
 */
class StockController extends Controller
{
    /**
     * @Route("/stocks", name="stocks_home")
     * @Template()
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $request)
    {
        $datatable = $this->get('rbs_erp.sales.datatable.stock');
        $datatable->buildDatatable();

        if ('POST' === $request->getMethod()) {
            $stockHistory = new StockHistory();
            $form = $this->createForm(new StockHistoryForm(), $stockHistory);
            $form->handleRequest($request);

            if ($form->isValid()) {
                return $this->save($request, $form, $stockHistory);
            }
        }

        return $this->render('RbsSalesBundle:Stock:index.html.twig', array(
            'datatable' => $datatable
        ));
    }

    /**
     * Lists all Category entities.
     *
     * @Route("/stocks_list_ajax", name="stocks_list_ajax", options={"expose"=true})
     * @Method("GET")
     */
    public function listAjaxAction()
    {
        $datatable = $this->get('rbs_erp.sales.datatable.stock');
        $datatable->buildDatatable();

        $query = $this->get('sg_datatables.query')->getQueryFrom($datatable);
        /** @var QueryBuilder $qb */
        $function = function ($qb) {
            $qb->join("stocks.item", 'i');
            $qb->andWhere("stocks.deletedAt IS NULL");
            // Show only Sales Bundle
            $qb->join("i.bundles", 'bundles');
            $qb->andWhere("bundles.id = :bundle")->setParameter('bundle', RbsSalesBundle::ID);
        };
        $query->addWhereAll($function);

        return $query->getResponse();
    }

    /**
     * find stock item ajax
     * @Route("find_stock_item_ajax", name="find_stock_item_ajax", options={"expose"=true})
     * @param Request $request
     * @return Response
     */
    public function findItemAction(Request $request)
    {
        $item = $request->request->get('item');

        $stock = $this->getDoctrine()->getRepository('RbsSalesBundle:Stock')->findOneBy(array(
            'item' => $item
        ));

        $response = array(
            'onHand'    => $stock->getOnHand(),
            'onHold'    => $stock->getOnHold(),
            'available' => $stock->isAvailableOnDemand(),
            'price'     => $stock->getItem()->getPrice(),
            'itemUnit'  => $stock->getItem()->getItemUnit(),
        );

        return new JsonResponse($response);
    }

    /**
     * @param $stock
     */
    protected function checkAvailableOnDemand(Stock $stock)
    {
        if ($stock->getOnHand() < $stock->getOnHold()) {
            $stock->setAvailableOnDemand(0);
            $this->getDoctrine()->getRepository('RbsSalesBundle:Stock')->update($stock);
        } elseif ($stock->getOnHand() >= $stock->getOnHold()) {
            $stock->setAvailableOnDemand(1);
            $this->getDoctrine()->getRepository('RbsSalesBundle:Stock')->update($stock);
        }
    }

    /**
     * @Route("/stock-create", name="stock_create", options={"expose"=true})
     * @Template("RbsSalesBundle:Stock:new.html.twig")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function createAction(Request $request)
    {
        $stockHistory = new StockHistory();
        $form = $this->createForm(new StockHistoryForm(), $stockHistory);
        $stockID = $request->query->all()['id'];
        $stock = $this->getDoctrine()->getRepository('RbsSalesBundle:Stock')->find($stockID);

        return array(
            'form' => $form->createView(),
            'stock' => $stock
        );
    }

    /**
     * @Route("/stock-history", name="stock_history", options={"expose"=true})
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function stockHistoryAction(Request $request)
    {
        $stock = $request->query->all()['id'];
        $stockHistories = $this->getDoctrine()->getRepository('RbsSalesBundle:StockHistory')->findBy(
            array('stock' => $stock), array('id' => 'DESC'), 10
        );

        return $this->render('RbsSalesBundle:Stock:history.html.twig', array(
            'stockHistories' => $stockHistories,
            'stock' => $this->getDoctrine()->getRepository('RbsSalesBundle:Stock')->find($stock)
        ));
    }

    /**
     * @Route("/stock-available", name="stock_available", options={"expose"=true})
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function stockAvailableAction(Request $request)
    {
        $stockId = $request->query->all()['id'];
        $stock = $this->getDoctrine()->getRepository('RbsSalesBundle:Stock')->find($stockId);

        $available = $this->isAvailable($stock);

        $stock->setAvailableOnDemand($available);
        $this->getDoctrine()->getRepository('RbsSalesBundle:Stock')->update($stock);

        $this->get('session')->getFlashBag()->add(
            'success', 'Stock Item Available Set Successfully!'
        );
        return $this->redirect($this->generateUrl('stocks_home'));
    }

    /**
     * @param Stock $stock
     * @return int
     */
    protected function isAvailable(Stock $stock)
    {
        if ($stock->isAvailableOnDemand()) {
            $available = 0;
            return $available;
        } else {
            $available = 1;
            return $available;
        }
    }

    /**
     * @param Request $request
     * @param $form
     * @param $stockHistory
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    protected function save(Request $request, $form, $stockHistory)
    {
        $stockID = (int)$request->request->all()['stock']['stockID'];
        $stock = $this->getDoctrine()->getRepository('RbsSalesBundle:Stock')->find($stockID);
        $quantity = $form->getData()->getQuantity();
        $quantity = $stock->getOnHand() + $quantity;
        $stock->setOnHand($quantity);
        $stockHistory->setStock($stock);

        $this->getDoctrine()->getRepository('RbsSalesBundle:Stock')->update($stock);
        $this->getDoctrine()->getRepository('RbsSalesBundle:StockHistory')->create($stockHistory);
        $this->checkAvailableOnDemand($stock);

        $this->get('session')->getFlashBag()->add(
            'success', 'Stock Item Quantity Add Successfully!'
        );
        return $this->redirect($this->generateUrl('stocks_home'));
        // if not user redirect url it take value again and again
    }
}