<?php

namespace Rbs\Bundle\SalesBundle\Controller;

use Doctrine\ORM\QueryBuilder;
use Rbs\Bundle\CoreBundle\Entity\Depo;
use Rbs\Bundle\CoreBundle\Entity\Item;
use Rbs\Bundle\CoreBundle\Entity\ItemType;
use Rbs\Bundle\CoreBundle\Entity\Location;
use Rbs\Bundle\CoreBundle\Repository\DepoRepository;
use Rbs\Bundle\SalesBundle\Entity\Agent;
use Rbs\Bundle\SalesBundle\Entity\DailyDepotStock;
use Rbs\Bundle\SalesBundle\Entity\Order;
use Rbs\Bundle\SalesBundle\Entity\OrderChickTemp;
use Rbs\Bundle\SalesBundle\Entity\OrderIncentiveFlag;
use Rbs\Bundle\SalesBundle\Entity\OrderItem;
use Rbs\Bundle\SalesBundle\Entity\OrderItemChickTemp;
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
     * @Route("/chick_orders", name="chick_orders_home")
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @JMS\Secure(roles="ROLE_DEPO_USER, ROLE_ORDER_VIEW, ROLE_ORDER_CREATE, ROLE_ORDER_EDIT, ROLE_ORDER_APPROVE, ROLE_ORDER_CANCEL")
     */
    public function chickOrdersAction()
    {
        if($this->isGranted('ROLE_DEPO_USER') and !$this->isGranted('ROLE_ADMIN')){
            $datatable = $this->get('rbs_erp.sales.datatable.chick.order.depo');
        }else{
            $datatable = $this->get('rbs_erp.sales.datatable.chick.order');
        }
        $datatable->buildDatatable();

        return $this->render('RbsSalesBundle:Order:index-chick.html.twig', array(
            'datatable' => $datatable,
        ));
    }

    /**
     * @Route("/chick_orders_list_ajax", name="chick_orders_list_ajax", options={"expose"=true})
     * @Method("GET")
     * @JMS\Secure(roles="ROLE_DEPO_USER, ROLE_ORDER_VIEW, ROLE_ORDER_CREATE, ROLE_ORDER_EDIT, ROLE_ORDER_APPROVE, ROLE_ORDER_CANCEL")
     */
    public function chickListAjaxAction()
    {
        if($this->isGranted('ROLE_DEPO_USER') and !$this->isGranted('ROLE_ADMIN')){
            $datatable = $this->get('rbs_erp.sales.datatable.chick.order.depo');
        }else{
            $datatable = $this->get('rbs_erp.sales.datatable.chick.order');
        }
        $datatable->buildDatatable();
        $query = $this->get('sg_datatables.query')->getQueryFrom($datatable);

        /** @var QueryBuilder $qb */
        $function = function($qb)
        {
            if($this->isGranted('ROLE_DEPO_USER')){
                $qb->join('sales_orders.depo', 'd');
                $qb->join('d.users', 'u');
                $qb->andWhere('u.id = :user');
                $qb->setParameter('user', $this->getUser()->getId());
            }
            $qb->andWhere('sales_orders.orderType = :type');
            $qb->setParameter('type', Order::ORDER_TYPE_CHICK);
        };
        $query->addWhereAll($function);

        return $query->getResponse();
    }


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
        $depo = null;
        $dailyStock = array();
        $date = $request->query->get('date') ? date('Y-m-d', strtotime($request->query->get('date'))) : null;
        $chickItems = $this->getChickItems();
        if ($itemId = $request->query->get('item')) {
            $item = $this->getDoctrine()->getRepository('RbsCoreBundle:Item')->find($itemId);
        }
        if ($depotId = $request->query->get('depot')) {
            $depo = $this->getDoctrine()->getRepository('RbsCoreBundle:Depo')->find($depotId);
        }
        $depots = $this->getDoctrine()->getRepository('RbsCoreBundle:Depo')->getAllActiveDepotForChick();

        if ($item && $date) {
            $orderItemResult = $this->getChickOrders($date, $item, $depo, [Order::ORDER_STATE_PENDING, Order::ORDER_STATE_PROCESSING]);
            $regions = $this->getRegions();

            $dailyStock = $this->getDoctrine()->getRepository('RbsSalesBundle:DailyDepotStock')->getDailyStockByDateAndItem($date,$item);
        }


        return $this->render('RbsSalesBundle:ChickOrder:manage-order.html.twig', array(
                'orderItemResult' => $orderItemResult,
                'chickItems'      => $chickItems,
                'date'            => $request->query->get('date'),
                'regions'         => $regions,
                'selectedItem'    => $itemId,
                'selectedDepot'    => $depotId,
                'dailyStock'    => $dailyStock,
                'depots'    => $depots,
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
        $itemMrpPrice = $request->request->get('itemMrpPrice');
        $price = $request->request->get('orderItemPrice');
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
                    $orderItem->setMrpPrice($itemMrpPrice[$orderId][$orderItemId]);
                    $orderItem->setPrice($price[$orderId][$orderItemId]);
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
        $depo = null;
        $date = $request->query->get('date') ? date('Y-m-d', strtotime($request->query->get('date'))) : null;
        $chickItems = $this->getChickItems();
        if ($itemId = $request->query->get('item')) {
            $item = $this->getDoctrine()->getRepository('RbsCoreBundle:Item')->find($itemId);
        }

        if ($depotId = $request->query->get('depot')) {
            $depo = $this->getDoctrine()->getRepository('RbsCoreBundle:Depo')->find($depotId);
        }
        $depots = $this->getDoctrine()->getRepository('RbsCoreBundle:Depo')->getAllActiveDepotForChick();

        if ($item && $date) {
            $orderItemResult = $this->getChickOrders($date, $item, $depo, [Order::ORDER_STATE_PROCESSING, Order::ORDER_STATE_COMPLETE]);
            $regions = $this->getRegions();
        }

        return $this->render('RbsSalesBundle:ChickOrder:view-summary.html.twig', array(
                'orderItemResult' => $orderItemResult,
                'chickItems'      => $chickItems,
                'date'            => $request->query->get('date'),
                'regions'         => $regions,
                'selectedItem'    => $itemId,
                'selectedDepot'    => $depotId,
                'depots'    => $depots,
            )
        );
    }

    private function getRegions() {
        $regionResult = $this->getDoctrine()->getRepository('RbsCoreBundle:Location')->findBy(array('level' => 3), array('name'=>'ASC'));
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

    private function getChickOrders($date, $item, $depot, $orderState) {
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
        if($depot){

            $qb->andWhere('o.depo = :depo')->setParameter('depo', $depot);
        }

        //$qb->andWhere('o.orderState IN :orderState')->setParameter('orderState', $orderState);
        $qb->andWhere($qb->expr()->in('o.orderState', ':orderState'))->setParameter('orderState', $orderState);

//        $qb->orderBy('z.parentId');
        $qb->orderBy('z.name');
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
     * @Route("/orders/chick/{id}/add", name="order_chick_add")
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @JMS\Secure(roles="ROLE_CHICK_ORDER_MANAGE")
     */
    public function addAction(Depo $depo, Request $request)
    {

        set_time_limit(0);
        ini_set('memory_limit','1024M');
        $em = $this->getDoctrine()->getManager();
        $date = $request->query->get('order_date') ? date('Y-m-d H:i:s', strtotime($request->query->get('order_date'))) : date('Y-m-d H:i:s', time());

        $locationsRegions = $this->getDoctrine()->getRepository('RbsCoreBundle:Location')->getRegionsForChick();
        $locationsDistricts = $this->getDoctrine()->getRepository('RbsCoreBundle:Location')->getDistrictsForChick();
        $agentLists = $this->getDoctrine()->getRepository('RbsSalesBundle:Agent')->getChickAgentsListByDistrict();

        $dailyStocks = $this->getDoctrine()->getRepository('RbsSalesBundle:DailyDepotStock')->getDailyStock($date);

        $chickItems = $this->getChickItems();
        $depots = $this->getDepots();

        if ($request->query->get('order_date')){
            /** @var Location $district */
            foreach ($locationsDistricts as $district){
                if (array_key_exists($district['id'],$agentLists)){
                    /** @var Agent $agent */
                    foreach ($agentLists[$district['id']] as $agent){
                        $agentObj = $this->getDoctrine()->getRepository('RbsSalesBundle:Agent')->find($agent['id']);
                       if(!$this->getChickOrdersByDateAgentDepotItem($date,$agentObj,$depo)){
                           $order = new OrderChickTemp();
                           $order->setAgent($agentObj);
                           $order->setDepo($depo);
                           $order->setOrderType(Order::ORDER_TYPE_CHICK);
                           $order->setLocation($agentObj->getUser()->getUpozilla()?$agentObj->getUser()->getUpozilla():null);
                           $order->setCreatedAt(new \DateTime($date));

                           $em->persist($order);


                           $this->insertOrderItem($order, $chickItems);
                       }
                    }
                    $em->flush();
                }
            }

            $addedData = $this->getTempChickOrdersByDateDepot($date, $depo);
        }

        return $this->render('RbsSalesBundle:ChickOrder:add-check-order.html.twig', array(
                'date'            => $request->query->get('order_date'),
                'orderDate'            => $date,
                'agentsList'         => $agentLists,
                'chickItems'         => $chickItems,
                'depot'         => $depo,
                'ordersTemp'         => $addedData,
                'dailyStocks'         => $dailyStocks,
                'locationsRegions' => $locationsRegions,
                'locationsDistricts' => $locationsDistricts,
            )
        );
    }


    /**
     * @Route("/orders/chick/list", name="order_chick_lists")
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @JMS\Secure(roles="ROLE_CHICK_ORDER_MANAGE, ROLE_DEPO_USER")
     */
    public function orderListAction( Request $request)
    {

        set_time_limit(0);
        ini_set('memory_limit','1024M');
        $data = array();
        $form = $this->createFormBuilder();
        $form->add('depot', 'entity', array(
                'class' => 'RbsCoreBundle:Depo',
                'placeholder' => 'Select Depo',
                'property' => 'name',
                'required'=>true,
                'query_builder' => function (DepoRepository $repository)
                {
                    return $repository->createQueryBuilder('d')
                        ->where("d.depotType = :depotType")
                        ->setParameter('depotType', Depo::DEPOT_TYPE_CHICK);


                }
            ))
            ->add('order_date', 'date', array(
                'widget' => 'single_text',
                'html5' => false,
                'format' => 'dd-MM-yyyy',
                'attr' => array(
                    'autocomplete'=>'off',
                    'class' => 'date-picker'
                )
            ))
            ->add('submit','submit',array('attr'=>array('class'=>'btn btn-primary'),'label'=>'Submit'));
        $form = $form->getForm();

        $date = $request->request->get('order_date') ? date('Y-m-d H:i:s', strtotime($request->query->get('order_date'))) : date('Y-m-d H:i:s', time());
        $addedData = array();

        $locationsRegions = $this->getDoctrine()->getRepository('RbsCoreBundle:Location')->getRegionsForChick();
        $locationsDistricts = $this->getDoctrine()->getRepository('RbsCoreBundle:Location')->getDistrictsForChick();
        $locationsUpazilas = $this->getDoctrine()->getRepository('RbsCoreBundle:Location')->getUpozillas();
        $locationsDistrictByUpazilla = $this->getDoctrine()->getRepository('RbsCoreBundle:Location')->getDistrictByUpozillas();
        $agentLists = $this->getDoctrine()->getRepository('RbsSalesBundle:Agent')->getChickAgentsListByDistrict();

        $dailyStocks = array();

        $chickItems = $this->getChickItems();
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            $data = $form->getData();
            $date = $data['order_date'] ? $data['order_date'] : date('Y-m-d H:i:s', time());
            if ($data['order_date']){


                $addedData = $this->getFinalChickOrdersByDateDepot($date, $data['depot']);


                $onlyDate = $date->format('Y-m-d');
                $dailyStocks = $this->getDoctrine()->getRepository('RbsSalesBundle:DailyDepotStock')->getDailyStock($onlyDate);
            }
        }

        return $this->render('RbsSalesBundle:ChickOrder:list-check-order.html.twig', array(
                'depot'            => $data?$data['depot']:null,
                'orderDate'            => $date,
                'agentsList'         => $agentLists,
                'chickItems'         => $chickItems,
                'ordersTemp'         => $addedData,
                'dailyStocks'         => $dailyStocks,
                'locationsRegions' => $locationsRegions,
                'locationsDistricts' => $locationsDistricts,
                'locationsUpazilas' => $locationsUpazilas,
                'locationsDistrictByUpazilla' => $locationsDistrictByUpazilla,
                'form' => $form->createView(),
            )
        );
    }

    private function insertOrderItem(OrderChickTemp $order,$items)
    {
        $em = $this->getDoctrine()->getManager();
        /** @var Item $item */
       foreach ($items as $item){
           $price = $this->getDoctrine()->getRepository('RbsCoreBundle:ItemPrice')->getCurrentPrice(
               $item, $order->getDepo()->getLocation()
           );
           $mrpPrice = $this->getDoctrine()->getRepository('RbsCoreBundle:ItemPrice')->getCurrentMrpPrice(
               $item, $order->getDepo()->getLocation()
           );
           $orderItem = new OrderItemChickTemp();
           $orderItem->setItem($item);
           $orderItem->setOrder($order);
           $orderItem->setQuantity(0);
           $orderItem->setPrice($price);
           $orderItem->setMrpPrice($mrpPrice);
           $orderItem->setTotalAmount(0);
           $em->persist($orderItem);
       }
       $em->flush();

    }

    private function getChickOrdersByDateAgentDepotItem($date, $agent, $depot) {
        $repo = $this->getDoctrine()->getRepository('RbsSalesBundle:OrderChickTemp');
        $qb = $repo->createQueryBuilder('o');

        $qb->select('o.id');
        $qb->join('o.orderItems', 'oi');

        $qb->where($qb->expr()->between('o.createdAt', ':start', ':end'));
        $qb->setParameters(array('start' => $date . ' 00:00:00', 'end' => $date . ' 23:59:59'));

        $qb->andWhere('o.agent = :agent');
        $qb->setParameter('agent', $agent);

        $qb->andWhere('o.depo = :depo');
        $qb->setParameter('depo', $depot);

        $result = $qb->getQuery()->getResult();

        return $result;
    }

    private function getTempChickOrdersByDateDepot($date, $depot) {
        $repo = $this->getDoctrine()->getRepository('RbsSalesBundle:OrderItemChickTemp');
        $qb = $repo->createQueryBuilder('oi');
        $qb->select('o.id as oId, oi.id as oiId, oi.quantity as quantity, a.id as aId, i.id as iId, d.id as dId');
        $qb->join('oi.order', 'o');
        $qb->join('oi.item','i');
        $qb->join('o.agent','a');
        $qb->join('o.depo','d');

        $qb->where($qb->expr()->between('o.createdAt', ':start', ':end'));
        $qb->setParameters(array('start' => $date , 'end' => $date ));

        $qb->andWhere('o.depo = :depot');
        $qb->setParameter('depot', $depot);

        $results = $qb->getQuery()->getResult();
        $dataArray = array();

        foreach ($results as $result){
            $dataArray[$result['aId']][$result['iId']]= $result;
        }
//        var_dump($dataArray);die;
        return $dataArray;
    }

    private function getFinalChickOrdersByDateDepot($date, $depot) {
        $repo = $this->getDoctrine()->getRepository('RbsSalesBundle:OrderItem');
        $qb = $repo->createQueryBuilder('oi');
        $qb->select('o.id as oId, oi.id as oiId, SUM(oi.quantity) as quantity, a.id as aId, i.id as iId, d.id as dId, l.parentId as lId');
        $qb->join('oi.order', 'o');
        $qb->join('oi.item','i');
        $qb->join('o.agent','a');
        $qb->join('o.depo','d');
        $qb->join('o.location','l');

        $qb->where($qb->expr()->between('o.createdAt', ':start', ':end'));
        $qb->setParameters(array('start' => $date , 'end' => $date ));

        $qb->andWhere('o.orderState=:orderSate');
        $qb->setParameter('orderSate', Order::ORDER_STATE_PROCESSING);
        $qb->andWhere('o.deliveryState=:deliveryState');
        $qb->setParameter('deliveryState', Order::DELIVERY_STATE_READY);


        $qb->andWhere('o.depo = :depot');
        $qb->setParameter('depot', $depot);

        $qb->groupBy('d.id, a.id, i.id, o.createdAt');

        $results = $qb->getQuery()->getResult();
        $dataArray = array();

        foreach ($results as $result){
            $dataArray[$result['lId']][$result['aId']][$result['iId']]= $result;
        }
        return $dataArray;
    }


    /**
     * update order and order item ajax
     * @Route("update_chick_order_item_temp_ajax/{order}/{orderItem}/{stock}", name="update_chick_order_item_temp_ajax", options={"expose"=true})
     * @param Request $request
     * @return Response
     * @JMS\Secure(roles="ROLE_CHICK_ORDER_MANAGE")
     */
    public function updateChickOrderItemTempQuantityAction(Request $request, OrderChickTemp $order, OrderItemChickTemp $orderItem, DailyDepotStock $stock)
    {
        $itemQuantity = $request->request->get('quantity');
        $em = $this->getDoctrine()->getManager();

        $previousOrderItemQuantity = $orderItem->getQuantity();


//        $order->setT($stock->getOnHand() + $stockItemOnHand);
        if($stock->getOnHand()>=$itemQuantity){
            $orderItem->setQuantity($itemQuantity);
            $orderItem->calculateTotalAmount(true);

            $order->setTotalAmount($order->getItemsTotalAmount());

            $stock->setOnHold(($stock->getOnHold() - $previousOrderItemQuantity) + $itemQuantity);

            $em->persist($orderItem);
            $em->persist($order);
            $em->flush();
        }

        $response = array(
            'itemQuantity'     => $orderItem->getQuantity(),
            'stockRemainingQuantity'     => $stock->getOnHand()- $stock->getOnHold(),
        );

        return new JsonResponse($response);
    }


    /**
     * update final order and order item ajax
     * @Route("update_final_chick_order_item_ajax/{order}/{orderItem}/{stock}", name="update_final_chick_order_item_ajax", options={"expose"=true})
     * @param Request $request
     * @return Response
     * @JMS\Secure(roles="ROLE_CHICK_ORDER_MANAGE")
     */
    public function updateFinalChickOrderItemQuantityAction(Request $request, Order $order, OrderItem $orderItem, DailyDepotStock $stock)
    {
        $itemQuantity = $request->request->get('quantity');
        $itemMrpPrice = $request->request->get('itemMrpPrice');
        $itemPrice = $request->request->get('itemPrice');
        $em = $this->getDoctrine()->getManager();

        $previousOrderItemQuantity = $orderItem->getQuantity();


        if($stock->getOnHand()>=(($stock->getOnHold() - $previousOrderItemQuantity) + $itemQuantity)){
            $orderItem->setMrpPrice($itemMrpPrice);
            $orderItem->setPrice($itemPrice);
            $orderItem->setQuantity($itemQuantity);
            $orderItem->calculateTotalAmount(true);

            $order->setTotalAmount($order->getItemsTotalAmount());

            $stock->setOnHold(($stock->getOnHold() - $previousOrderItemQuantity) + $itemQuantity);

            $em->persist($orderItem);
            $em->persist($order);
            $em->persist($stock);
            $em->flush();
        }

        $response = array(
            'itemQuantity'     => $orderItem->getQuantity(),
            'stockRemainingQuantity'     => $stock->getOnHand()- $stock->getOnHold(),
        );

        return new JsonResponse($response);
    }

    /**
     * @Route("/orders/chick/{id}/save", name="order_chick_save")
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @JMS\Secure(roles="ROLE_CHICK_ORDER_MANAGE")
     */

    public function createOrderAction(Depo $depo, Request $request)
    {

        $date = $request->request->get('date') ? date('Y-m-d', strtotime($request->request->get('date'))) : date('Y-m-d', time());
//       $total_amount = $request->request->get('total_amount');
        $data= array();
        $orderChickTemps = $this->getDoctrine()->getRepository('RbsSalesBundle:OrderChickTemp')->getOrderChickTempByOrderDate($date, $depo);

        /** @var OrderChickTemp $orderChickTemp */
        foreach ($orderChickTemps as $orderChickTemp){
            if($orderChickTemp->getTotalAmount()>0){
                $order = new Order();

                $order->setAgent($orderChickTemp->getAgent());
                $order->setDepo($orderChickTemp->getDepo());
                $order->setTotalAmount($orderChickTemp->getTotalAmount());
                $order->setLocation($orderChickTemp->getAgent()->getUser()->getUpozilla());
                $order->setDeliveryState(Order::DELIVERY_STATE_READY);
                $order->setOrderState(Order::ORDER_STATE_PROCESSING);
                $order->setPaymentState(Order::PAYMENT_STATE_PENDING);
                $order->setOrderType(Order::ORDER_TYPE_CHICK);
                $order->setOrderVia('SYSTEM');
                $order->setCreatedAt(new \DateTime($date) );

                $this->getDoctrine()->getManager()->persist($order);

                /** @var OrderItemChickTemp $orderChickItemTemp */
                foreach ($orderChickTemp->getOrderItems() as $orderChickItemTemp){
                    if($orderChickItemTemp->getQuantity()>0){
                        $orderItem = new OrderItem();
                        $orderItem->setQuantity($orderChickItemTemp->getQuantity());
                        $orderItem->setPrice($orderChickItemTemp->getPrice());
                        $orderItem->setMrpPrice($orderChickItemTemp->getMrpPrice());
                        $orderItem->setTotalAmount($orderChickItemTemp->getTotalAmount());
                        $orderItem->setItem($orderChickItemTemp->getItem());
                        $orderItem->setOrder($order);
                        $this->getDoctrine()->getManager()->persist($orderItem);
                    }

                    $orderChickTemp->removeOrderItem($orderChickItemTemp);

                }

                $data[]= $order->getId();
            }
            $this->getDoctrine()->getManager()->remove($orderChickTemp);
       }
       if (in_array(null, $data)){
           $this->flashMessage('success', 'Order add Successfully.');
       }else{
           $this->flashMessage('success', 'Order update Successfully.');
       }


        $this->getDoctrine()->getManager()->flush();

        return $this->redirect($this->generateUrl('chick_orders_home'));
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