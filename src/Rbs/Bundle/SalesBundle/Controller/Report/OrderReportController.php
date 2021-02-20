<?php
/**
 * Created by PhpStorm.
 * User: hasan
 * Date: 7/6/20
 * Time: 4:14 PM
 */

namespace Rbs\Bundle\SalesBundle\Controller\Report;

use Doctrine\ORM\QueryBuilder;
use Rbs\Bundle\SalesBundle\Form\Search\Type\DistrictItemMonthSearchType;
use Rbs\Bundle\SalesBundle\Form\Search\Type\FeedOrderItemReportType;
use Rbs\Bundle\SalesBundle\Form\Search\Type\FeedOrderReportType;
use Rbs\Bundle\SalesBundle\Form\Search\Type\SearchType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use JMS\SecurityExtraBundle\Annotation as JMS;
use Symfony\Component\HttpFoundation\Response;

class OrderReportController extends Controller
{
    /**
     * @Route("/report/daily/feed/order", name="report_daily_feed_order")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @JMS\Secure(roles="ROLE_FEED_ORDER_REPORT")
     */
    public function getDailyFeedOrder(Request $request){

        $form = new FeedOrderReportType();
        $data = $request->query->get($form->getName());
        $pdf_create = $request->query->get('pdf_create');
        $submit = $request->query->get('submit');
        $formSearch = $this->createForm($form, $data);
        $dailyOrders=array();
        $paymentAmountViaOrders=array();
        if ('GET' === $request->getMethod() && $submit) {
            $formSearch->handleRequest($request);
            $formSearch->submit($data);
            if ($formSearch->isValid()) {
                $dailyOrders = $this->getDoctrine()->getRepository('RbsSalesBundle:Order')->getDailyFeedOrder($data);
                $paymentAmountViaOrders = $this->getDoctrine()->getRepository('RbsSalesBundle:Order')->getPaymentAmountWithOrder($data);
             }
        }
        $depots = $this->getDoctrine()->getRepository('RbsCoreBundle:Depo')->getActiveDepotForFeed($data);
        $itemCategories = $this->getDoctrine()->getRepository('RbsCoreBundle:Category')->getAllActiveCategory();

            return $this->render('RbsSalesBundle:Report/FeedOrder:daily-feed-order.html.twig', array(
                'formSearch' => $formSearch->createView(),
                'data' => $data,
                'orders' => $dailyOrders,
                'paymentAmountViaOrders' => $paymentAmountViaOrders,
                'depots' => $depots,
                'itemCategories' => $itemCategories,
            ));

    }

    /**
     * @Route("/report/feed/order", name="report_feed_order")
     * @param Request $request
     * @Method("GET")
     * @JMS\Secure(roles="ROLE_FEED_ORDER_REPORT")
     */
    public function getFeedOrderReport(Request $request){

        $form = new FeedOrderReportType();
        $data = $request->query->get($form->getName());
        $pdf_create = $request->query->get('pdf_create');
        $submit = $request->query->get('submit');
        $formSearch = $this->createForm($form, $data);
        $dailyOrders=array();
        $paymentAmountViaOrders=array();
        if ('GET' === $request->getMethod() && $submit) {
            $formSearch->handleRequest($request);
            $formSearch->submit($data);
            if ($formSearch->isValid()) {
                $dailyOrders = $this->getDoctrine()->getRepository('RbsSalesBundle:Order')->getFeedOrderReport($data);
                $paymentAmountViaOrders = $this->getDoctrine()->getRepository('RbsSalesBundle:Order')->getPaymentAmountWithOrderForReport($data);
             }
        }
        $depots = $this->getDoctrine()->getRepository('RbsCoreBundle:Depo')->getActiveDepotForFeed($data);

            return $this->render('RbsSalesBundle:Report/FeedOrder:feed-order-report.html.twig', array(
                'formSearch' => $formSearch->createView(),
                'data' => $data,
                'orders' => $dailyOrders,
                'paymentAmountViaOrders' => $paymentAmountViaOrders,
                'depots' => $depots,
            ));

    }

    /**
     * @Route("/report/feed/order/excel", name="report_feed_order_excel", options={"expose"=true})
     * @param Request $request
     * @return Response
     * @JMS\Secure(roles="ROLE_FEED_ORDER_REPORT")
     */
    public function getFeedOrderReportExcel(Request $request){

        $form = new FeedOrderReportType();
        $data = $request->get($form->getName());

        $dailyOrders = $this->getDoctrine()->getRepository('RbsSalesBundle:Order')->getFeedOrderReport($data);
        $paymentAmountViaOrders = $this->getDoctrine()->getRepository('RbsSalesBundle:Order')->getPaymentAmountWithOrderForReport($data);
        $depots = $this->getDoctrine()->getRepository('RbsCoreBundle:Depo')->getActiveDepotForFeed($data);

        $html =     $this->renderView('RbsSalesBundle:Report/FeedOrder:_content-feed-order-report.html.twig', array(
            'data' => $data,
            'orders' => $dailyOrders,
            'paymentAmountViaOrders' => $paymentAmountViaOrders,
            'depots' => $depots,
        ));
        $file="dailyOrderReport_".time().".xls";
        $test="$html";
        header("Content-type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename=$file");
        echo $test;die;
    }



    /**
     * @Route("/report/region/feed/order", name="report_feed_order_region_wise")
     * @param Request $request
     * @Method("GET")
     * @JMS\Secure(roles="ROLE_FEED_ORDER_REPORT")
     */
    public function getFeedOrderReportRegionWise(Request $request){

        $form = new FeedOrderReportType();
        $data = $request->query->get($form->getName());
        $pdf_create = $request->query->get('pdf_create');
        $submit = $request->query->get('submit');
        $formSearch = $this->createForm($form, $data);
        $dailyOrders=array();
        $paymentAmountViaOrders=array();
        if ('GET' === $request->getMethod() && $submit) {
            $formSearch->handleRequest($request);
            $formSearch->submit($data);
            if ($formSearch->isValid()) {
                $dailyOrders = $this->getDoctrine()->getRepository('RbsSalesBundle:Order')->getFeedOrderReportZoneWise($data);
                $paymentAmountViaOrders = $this->getDoctrine()->getRepository('RbsSalesBundle:Order')->getPaymentAmountWithOrderForReport($data);
            }
        }
        $locationsRegions = $this->getDoctrine()->getRepository('RbsCoreBundle:Location')->getRegionsForChick();

        return $this->render('RbsSalesBundle:Report/FeedOrder:feed-order-report-region.html.twig', array(
            'formSearch' => $formSearch->createView(),
            'data' => $data,
            'orders' => $dailyOrders,
            'paymentAmountViaOrders' => $paymentAmountViaOrders,
            'locationsRegions' => $locationsRegions,
        ));

    }


    /**
     * @Route("/report/region/feed/order/excel", name="report_feed_order_region_wise_excel", options={"expose"=true})
     * @param Request $request
     * @return Response
     * @JMS\Secure(roles="ROLE_FEED_ORDER_REPORT")
     */
    public function getFeedOrderReportRegionWiseExcel(Request $request){

        $form = new FeedOrderReportType();
        $data = $request->get($form->getName());

        $dailyOrders = $this->getDoctrine()->getRepository('RbsSalesBundle:Order')->getFeedOrderReportZoneWise($data);
        $paymentAmountViaOrders = $this->getDoctrine()->getRepository('RbsSalesBundle:Order')->getPaymentAmountWithOrderForReport($data);
        $locationsRegions = $this->getDoctrine()->getRepository('RbsCoreBundle:Location')->getRegionsForChick();

        $html =     $this->renderView('RbsSalesBundle:Report/FeedOrder:_content-feed-order-report-region.html.twig', array(
            'data' => $data,
            'orders' => $dailyOrders,
            'paymentAmountViaOrders' => $paymentAmountViaOrders,
            'locationsRegions' => $locationsRegions,
        ));
        $file="dailyOrderReportRegionWise_".time().".xls";
        $test="$html";
        header("Content-type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename=$file");
        echo $test;die;
    }


    /**
     * @Route("/report/daily/feed/order/item", name="report_daily_feed_order_item")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @JMS\Secure(roles="ROLE_FEED_ORDER_REPORT")
     */
    public function getDailyFeedOrderItem(Request $request){

        $form = new FeedOrderItemReportType();
        $data = $request->query->get($form->getName());
        $submit = $request->query->get('submit');
        $formSearch = $this->createForm($form, $data);
        $dailyOrders=array();
        if ('GET' === $request->getMethod() && $submit) {
            $formSearch->handleRequest($request);
            $formSearch->submit($data);
            if ($formSearch->isValid()) {
                $dailyOrders = $this->getDoctrine()->getRepository('RbsSalesBundle:Order')->getDailyFeedOrderItem($data);
             }
        }

            return $this->render('RbsSalesBundle:Report/FeedOrder:feed-order-item.html.twig', array(
                'formSearch' => $formSearch->createView(),
                'data' => $data,
                'orders' => $dailyOrders,
            ));

    }



}