<?php

namespace Rbs\Bundle\SalesBundle\Controller;

use Doctrine\ORM\QueryBuilder;
use Rbs\Bundle\CoreBundle\Entity\Depo;
use Rbs\Bundle\CoreBundle\Entity\Item;
use Rbs\Bundle\CoreBundle\Entity\ItemType;
use Rbs\Bundle\SalesBundle\Entity\Order;
use Rbs\Bundle\SalesBundle\Entity\OrderIncentiveFlag;
use Rbs\Bundle\SalesBundle\Entity\OrderItem;
use Rbs\Bundle\SalesBundle\Entity\Payment;
use Rbs\Bundle\SalesBundle\Form\Type\OrderForm;
use Rbs\Bundle\UserBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use JMS\SecurityExtraBundle\Annotation as JMS;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Order Controller.
 *
 */
class ChickOrderController extends BaseController
{
    /**
     * @Route("/orders/manage/chick", name="order_manage_chick")
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @JMS\Secure(roles="ROLE_CHICK_ORDER_MANAGE")
     */
    public function indexAction(Request $request)
    {
        $regions = array();
        $orderItemResult = array();
        $item = null;
        $date = $request->query->get('date') ? date('Y-m-d', strtotime($request->query->get('date'))) : null;
        $chickItems = $this->getChickItems();
        if ($itemId = $request->query->get('item')) {
            $item = $this->getDoctrine()->getRepository('RbsCoreBundle:Item')->find($itemId);
        }

        if ($item && $date) {
            $orderItemResult = $this->getChickOrders($date, $item, [Order::ORDER_STATE_PENDING]);
            $regions = $this->getRegions();
        }

        return $this->render('RbsSalesBundle:ChickOrder:manage-order.html.twig', array(
                'orderItemResult' => $orderItemResult,
                'chickItems'      => $chickItems,
                'date'            => $request->query->get('date'),
                'regions'         => $regions,
                'selectedItem'    => $itemId,
            )
        );
    }

    /**
     * @Route("/orders/manage/chick/save", name="order_manage_chick_save", options={"expose"=true})
     * @JMS\Secure(roles="ROLE_CHICK_ORDER_MANAGE")
     */
    public function saveAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $data = $request->request->get('orderItem');
        $deliver = $request->request->get('deliver');

        $output = ['skipped' => [], 'update' => []];

        try {
            foreach ($data as $orderId => $orderItem) {
                foreach ($orderItem as $orderItemId => $orderItemValue) {
                    /** @var OrderItem $orderItem */
                    if (!$orderItem = $em->getRepository('RbsSalesBundle:OrderItem')->find($orderItemId)) {
                        $output['skipped'][] = $orderItemId;
                        continue;
                    }

                    if (!$order = $em->getRepository('RbsSalesBundle:Order')->find($orderId)) {
                        continue;
                    }

                    $output['old'][$orderId] = $order->getItemsTotalAmount();

                    $output['update'][] = $orderItemId;
                    $orderItem->setQuantity((int) $orderItemValue);

                    $order->calculateOrderAmount();

                    $output['new'][$orderId] = $order->getTotalAmount();

                    if ($deliver == 'true') {
                        $order->setDeliveryState(Order::DELIVERY_STATE_READY);
                        $order->setOrderState(Order::ORDER_STATE_PROCESSING);
                        $order->setPaymentState(Order::PAYMENT_STATE_PENDING);
                    }

                    $em->persist($orderItem);
                    $em->persist($order);
                }

                $em->flush();
            }

        } catch (\Exception $e) {
            return new JsonResponse(['success' => false, 'message' => $e->getMessage()]);
        }

        return new JsonResponse(array_merge($output, ['success' => true]));
    }

    /**
     * @Route("/orders/view-summary/chick", name="order_manage_chick_summary", options={"expose"=true})
     * @JMS\Secure(roles="ROLE_CHICK_ORDER_MANAGE")
     */
    public function viewSummaryAction(Request $request)
    {
        $regions = array();
        $orderItemResult = array();
        $item = null;
        $date = $request->query->get('date') ? date('Y-m-d', strtotime($request->query->get('date'))) : null;
        $chickItems = $this->getChickItems();
        if ($itemId = $request->query->get('item')) {
            $item = $this->getDoctrine()->getRepository('RbsCoreBundle:Item')->find($itemId);
        }

        if ($item && $date) {
            $orderItemResult = $this->getChickOrders($date, $item, [Order::ORDER_STATE_PROCESSING, Order::ORDER_STATE_COMPLETE]);
            $regions = $this->getRegions();
        }

        return $this->render('RbsSalesBundle:ChickOrder:view-summary.html.twig', array(
                'orderItemResult' => $orderItemResult,
                'chickItems'      => $chickItems,
                'date'            => $request->query->get('date'),
                'regions'         => $regions,
                'selectedItem'    => $itemId,
            )
        );
    }

    private function getRegions() {
        $regionResult = $this->getDoctrine()->getRepository('RbsCoreBundle:Location')->findBy(array('level' => 3));
        $regions = array();
        foreach ($regionResult as $row) {
            $regions[$row->getId()] = $row->getName();
        }

        return $regions;
    }

    private function getDepots() {
        $depotResult = $this->getDoctrine()->getRepository('RbsCoreBundle:Depo')->findBy(array('deletedAt' => null));


        return $depotResult;
    }

    private function getChickItems() {
        $em = $this->getDoctrine()->getManager();

        return $em->getRepository('RbsCoreBundle:Item')->findBy(
            array(
                'itemType' => $em->getRepository('RbsCoreBundle:ItemType')->findOneBy(
                    array(
                        'itemType' => ItemType::Chick
                    )
                )
            )
        );
    }

    private function getChickOrders($date, $item, $orderState) {
        $repo = $this->getDoctrine()->getRepository('RbsSalesBundle:OrderItem');
        $qb = $repo->createQueryBuilder('oi');

        $qb->select('oi');
        $qb->join('oi.order', 'o');
        $qb->join('o.agent', 'a');
        $qb->join('a.user', 'u');
        $qb->join('u.profile', 'p');
        $qb->join('u.zilla', 'z');

        $qb->where($qb->expr()->between('o.createdAt', ':start', ':end'));
        $qb->setParameters(array('start' => $date . ' 00:00:00', 'end' => $date . ' 23:59:59'));

        $qb->andWhere('oi.item = :item')->setParameter('item', $item);

        //$qb->andWhere('o.orderState IN :orderState')->setParameter('orderState', $orderState);
        $qb->andWhere($qb->expr()->in('o.orderState', ':orderState'))->setParameter('orderState', $orderState);

        $qb->orderBy('z.parentId');
        $qb->addOrderBy('z.name');
        $qb->addOrderBy('p.fullName');
        $result = $qb->getQuery()->getResult();
        $orderItemResult = array();
        foreach ($result as $row) {
            $orderItemResult[$row->getOrder()->getAgent()->getUser()->getZilla()->getParentId()][] = $row;
        }

        return $orderItemResult;
    }

    private function getChickAgents($regionId) {
        $repo = $this->getDoctrine()->getRepository('RbsSalesBundle:Agent');
        $qb = $repo->createQueryBuilder('a');

        $qb->select('a');
        $qb->join('a.user', 'u');
        $qb->join('u.profile', 'p');
        $qb->join('u.zilla', 'z');
//        $qb->join('a.itemType', 'i');

        $qb->where('u.userType = :AGENT');
        $qb->setParameter('AGENT', User::AGENT);

        /*$qb->andWhere('i.itemType = :itemType');
        $qb->setParameter('itemType', ItemType::Chick);*/

        //$qb->andWhere('o.orderState IN :orderState')->setParameter('orderState', $orderState);
        $qb->andWhere($qb->expr()->in('z.parentId', ':parentId'))->setParameter('parentId', $regionId);

        $qb->orderBy('z.parentId');
        $qb->addOrderBy('z.name');
        $qb->addOrderBy('p.fullName');
        $result = $qb->getQuery()->getResult();
        $agentResult = array();
        foreach ($result as $row) {
            $agentResult[$row->getUser()->getZilla()->getParentId()][] = $row;
        }

        return $agentResult;
    }

    /**
     * @Route("/orders/chick/add", name="order_chick_add")
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @JMS\Secure(roles="ROLE_CHICK_ORDER_MANAGE")
     */
    public function addAction(Request $request)
    {
        $time = date('H:i:s', time());
        $date = $request->request->get('date') ? date('Y-m-d', strtotime($request->request->get('date')) ) .' '. $time : null;
        $getRegions = $request->request->get('region')? $request->request->get('region'):array();
        $regions = $this->getRegions();
        $agents = $this->getChickAgents($getRegions);
        $chickItems = $this->getChickItems();
        $depots = $this->getDepots();
        $addedOrders = $this->getChickOrdersByZone(date('Y-m-d', strtotime($request->request->get('date')) ), $getRegions,  [Order::ORDER_STATE_PENDING] );


        return $this->render('RbsSalesBundle:ChickOrder:add-check-order.html.twig', array(
                'date'            => $request->request->get('date'),
                'orderDate'            => $date,
                'regions'         => $regions,
                'selectedRegions'         => $getRegions,
                'agents'         => $agents,
                'chickItems'         => $chickItems,
                'depots'         => $depots,
                'zoneAllOrders'         => $addedOrders,
            )
        );
    }
    /**
     * @Route("/orders/chick/save", name="order_chick_save")
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @JMS\Secure(roles="ROLE_CHICK_ORDER_MANAGE")
     */

    public function createOrderAction(Request $request)
    {

        $quantities = $request->request->get('quantity');
        $order_id = $request->request->get('order_id');
        $orderItem_id = $request->request->get('orderItem_id');
        $agentId = $request->request->get('agentId');
        $depotId = $request->request->get('depot');
        $itemId = $request->request->get('item');
        $itemPrice = $request->request->get('itemPrice');
        $date = $request->request->get('order_date') ? date('Y-m-d H:i:s', strtotime($request->request->get('order_date'))) : date('Y-m-d H:i:s', time());
//       $total_amount = $request->request->get('total_amount');
        $data= array();
       foreach ($quantities as $key=>$quantity){
           if(!empty($quantity) && !empty($agentId[$key]) && !empty($depotId[$key]) && !empty($itemId[$key])){
               //var_dump( $item[$key]);die;
               $agent = $this->getDoctrine()->getRepository('RbsSalesBundle:Agent')->find(array('id' => $agentId[$key]));

               $itemObj = $this->getDoctrine()->getRepository('RbsCoreBundle:Item')->find(array('id' => $itemId[$key]));
               $depotObj = $this->getDoctrine()->getRepository('RbsCoreBundle:Depo')->find(array('id' => $depotId[$key]));


               if (!empty($orderItem_id[$key])){
                   $orderItem = $this->getDoctrine()->getRepository('RbsSalesBundle:OrderItem')->find(array('id' => $orderItem_id[$key]));
               }else{
                   $orderItem = new OrderItem();
               }

               $orderItem->setItem($itemObj);
               $orderItem->setQuantity($quantity);
               $orderItem->setPrice($itemPrice[$key]);

               if (!empty($order_id[$key])){
                   $order = $this->getDoctrine()->getRepository('RbsSalesBundle:Order')->find(array('id' => $order_id[$key]));
               }else{
                   $order = new Order();
               }

               $orderItem->setOrder($order);

               $order->addOrderItem($orderItem);
               $order->setAgent($agent);
               $order->setDepo($depotObj);
               $order->setLocation($agent->getUser()->getUpozilla());
               $order->setTotalAmount($order->getItemsTotalAmount());
               $order->setOrderState(Order::ORDER_STATE_PENDING);
               $order->setPaymentState(Order::PAYMENT_STATE_PENDING);
               $order->setDeliveryState(Order::DELIVERY_STATE_PENDING);
               $order->setOrderVia('SYSTEM');
               $order->setCreatedAt(new \DateTime($date) );

               $order->calculateOrderAmount();

               $this->getDoctrine()->getManager()->persist($orderItem);
               $this->getDoctrine()->getManager()->persist($order);
               $data[]= $order->getId();
           }
       }
       if (in_array(null, $data)){
           $this->flashMessage('success', 'Order add Successfully.');
       }else{
           $this->flashMessage('success', 'Order update Successfully.');
       }


        $this->getDoctrine()->getManager()->flush();

        return $this->redirect($this->generateUrl('order_chick_add'));
    }

    private function getChickOrdersByZone($date, $zone, $orderState) {
        $repo = $this->getDoctrine()->getRepository('RbsSalesBundle:OrderItem');
        $qb = $repo->createQueryBuilder('oi');

        $qb->select('oi');
        $qb->join('oi.order', 'o');
        $qb->join('o.agent', 'a');
        $qb->join('a.user', 'u');
        $qb->join('u.profile', 'p');
        $qb->join('u.zilla', 'z');

        $qb->where($qb->expr()->between('o.createdAt', ':start', ':end'));
        $qb->setParameters(array('start' => $date . ' 00:00:00', 'end' => $date . ' 23:59:59'));

        $qb->andWhere($qb->expr()->in('z.parentId', ':parentId'))->setParameter('parentId', $zone);
        $qb->andWhere($qb->expr()->in('o.orderState', ':orderState'))->setParameter('orderState', $orderState);

        $qb->orderBy('z.parentId');
        $qb->addOrderBy('z.name');
        $qb->addOrderBy('p.fullName');
        $result = $qb->getQuery()->getResult();
        $orderItemResult = array();
        foreach ($result as $row) {
            $orderItemResult[$row->getOrder()->getAgent()->getUser()->getZilla()->getParentId()][] = $row;
        }

        return $orderItemResult;
    }


    /**
     * find stock item ajax
     * @Route("find_item_price_depo_ajax/{item}/{depo}", name="find_item_price_depo_ajax", options={"expose"=true})
     * @param Request $request
     * @return Response
     * @JMS\Secure(roles="ROLE_CHICK_ORDER_MANAGE")
     */
    public function findItemPriceDepoAction(Request $request, Item $item, Depo $depo)
    {
        $em = $this->getDoctrine()->getManager();

        /** Getting Item Price */
        $price = 0;
        $orderItem = null;

            $price = $this->getDoctrine()->getRepository('RbsCoreBundle:ItemPrice')->getCurrentPrice(
                $item, $depo->getLocation()
            );

          $response = array(
                'price'     => number_format($price, 2),
            );

        return new JsonResponse($response);
    }

    /**
     * delete order ajax
     * @Route("delete_check_order_ajax/{order}", name="delete_check_order_ajax", options={"expose"=true})
     * @return Response
     * @JMS\Secure(roles="ROLE_CHICK_ORDER_MANAGE")
     */
    public function deleteCheckOrderAction($order)
    {

        $em = $this->getDoctrine()->getManager();
        $orderObj = $this->getDoctrine()->getRepository('RbsSalesBundle:Order')->find(array('id'=>$order));
        foreach ($orderObj->getOrderItems() as $item){
            $orderObj->removeOrderItem($item);
        }

        $em->remove($orderObj);
        $em->flush();
        return new JsonResponse(array('success'=>'Order successfully removed.'));
    }

}