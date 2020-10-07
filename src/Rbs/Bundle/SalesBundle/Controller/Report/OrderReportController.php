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
        if ('GET' === $request->getMethod() && $submit) {
            $formSearch->handleRequest($request);
            $formSearch->submit($data);
            if ($formSearch->isValid()) {
                $dailyOrders = $this->getDoctrine()->getRepository('RbsSalesBundle:Order')->getDailyFeedOrder($data);
             }
        }
        $depots = $this->getDoctrine()->getRepository('RbsCoreBundle:Depo')->getAllActiveDepotForFeed();
        $itemCategories = $this->getDoctrine()->getRepository('RbsCoreBundle:Category')->getAllActiveCategory();

            return $this->render('RbsSalesBundle:Report/FeedOrder:daily-feed-order.html.twig', array(
                'formSearch' => $formSearch->createView(),
                'data' => $data,
                'orders' => $dailyOrders,
                'depots' => $depots,
                'itemCategories' => $itemCategories,
            ));

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