<?php

namespace Rbs\Bundle\SalesBundle\Controller;

use Doctrine\ORM\QueryBuilder;
use Rbs\Bundle\CoreBundle\Entity\CoreSettings;
use Rbs\Bundle\CoreBundle\Entity\Depo;
use Rbs\Bundle\CoreBundle\Entity\Item;
use Rbs\Bundle\CoreBundle\Entity\ItemType;
use Rbs\Bundle\CoreBundle\Entity\Location;
use Rbs\Bundle\CoreBundle\Repository\DepoRepository;
use Rbs\Bundle\SalesBundle\Entity\Agent;
use Rbs\Bundle\SalesBundle\Entity\DailyDepotStock;
use Rbs\Bundle\SalesBundle\Entity\Delivery;
use Rbs\Bundle\SalesBundle\Entity\DeliveryItem;
use Rbs\Bundle\SalesBundle\Entity\Order;
use Rbs\Bundle\SalesBundle\Entity\OrderChickTemp;
use Rbs\Bundle\SalesBundle\Entity\OrderIncentiveFlag;
use Rbs\Bundle\SalesBundle\Entity\OrderItem;
use Rbs\Bundle\SalesBundle\Entity\OrderItemChickTemp;
use Rbs\Bundle\SalesBundle\Entity\Payment;
use Rbs\Bundle\SalesBundle\Entity\Vehicle;
use Rbs\Bundle\SalesBundle\Form\Type\ChickOrderEditForm;
use Rbs\Bundle\SalesBundle\Form\Type\ChickOrderForm;
use Rbs\Bundle\SalesBundle\Form\Type\OrderForm;
use Rbs\Bundle\SalesBundle\Form\Type\OrderWithoutSmsForm;
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
     * @JMS\Secure(roles="ROLE_DEPO_USER, ROLE_CHICK_ORDER_MANAGE")
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
     * @JMS\Secure(roles="ROLE_DEPO_USER, ROLE_ORDER_VIEW, ROLE_ORDER_CREATE, ROLE_ORDER_EDIT, ROLE_ORDER_APPROVE, ROLE_ORDER_CANCEL, ROLE_CHICK_ORDER_MANAGE")
     */
    public function chickListAjaxAction(Request $request)
    {
        if($this->isGranted('ROLE_DEPO_USER') and !$this->isGranted('ROLE_ADMIN')){
            $datatable = $this->get('rbs_erp.sales.datatable.chick.order.depo');
        }else{
            $datatable = $this->get('rbs_erp.sales.datatable.chick.order');
        }
        $datatable->buildDatatable();

        $dateFilter = $request->query->get('columns[5][search][value]', null, true);

        $columns = $request->query->get('columns');
        $columns[5]['search']['value'] = '';
        $request->query->set('columns', $columns);

        $query = $this->get('sg_datatables.query')->getQueryFrom($datatable);

        /** @var QueryBuilder $qb */
        $function = function($qb) use ($dateFilter)
        {
            if($this->isGranted('ROLE_DEPO_USER')){
                $qb->join('sales_orders.depo', 'd');
                $qb->join('d.users', 'u');
                $qb->andWhere('u.id = :user');
                $qb->setParameter('user', $this->getUser()->getId());
            }
            $qb->andWhere('sales_orders.orderType = :type');
            $qb->setParameter('type', Order::ORDER_TYPE_CHICK);

            if ($dateFilter) {
                $qb->andWhere('sales_orders.createdAt BETWEEN :fromDate AND :toDate')
                    ->setParameter('fromDate', date('Y-m-d 00:00:00', strtotime($dateFilter)))
                    ->setParameter('toDate', date('Y-m-d 23:59:59', strtotime($dateFilter)));
            }else{
                $qb->andWhere('sales_orders.createdAt BETWEEN :fromDate AND :toDate')
                    ->setParameter('fromDate', date('Y-m-d 00:00:00'))
                    ->setParameter('toDate', date('Y-m-d 23:59:59'));
            }
        };
        $query->addWhereAll($function);

        return $query->getResponse();
    }

    /**
     * @Route("/chick/order/create", name="chick_order_create_new", options={"expose"=true})
     * @Template("RbsSalesBundle:ChickOrder:new-order-create.html.twig")
     * @param Request $request
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @JMS\Secure(roles="ROLE_DEPO_USER, ROLE_CHICK_ORDER_MANAGE, ROLE_ORDER_EDIT, ROLE_ORDER_APPROVE")
     */
    public function createChickOrderAction(Request $request)
    {
        $order = new Order();
        $orderIncentiveFlag = new OrderIncentiveFlag();
        $allRequest = $request->request->get('order');
        $deliveryId = $request->request->get('delivery_search');
        $form = $this->createForm(new ChickOrderForm(), $order);
        if ('POST' === $request->getMethod()) {

            $form->handleRequest($request);

            /** @var OrderItem $item */
            foreach ($order->getOrderItems() as $item){
                if ($item->getQuantity() < 1) {
                    $this->flashMessage('error', 'Invalid Item Quantity');
                    goto a;
                }

                $price = $this->getDoctrine()->getRepository('RbsCoreBundle:ItemPrice')->getCurrentPrice(
                    $item->getItem(), $order->getDepo()->getLocation()
                );
                if ($price < 1) {
                    $this->flashMessage('error', 'Invalid Item Price');
                    goto a;
                }

            }

            if ($form->isValid()) {
                $delivery = null;

                if($deliveryId){
                    $delivery = $this->getDoctrine()->getRepository('RbsSalesBundle:Delivery')->find($deliveryId);
                }

                $currentTime = date('H:i:s',strtotime('now'));
                $requestDate = isset($allRequest['created_at'])?date('Y-m-d',strtotime($allRequest['created_at'])):date('Y-m-d',strtotime('now'));
                $orderDate = $requestDate.' '.$currentTime;

                $em = $this->getDoctrine()->getManager();
                $order->setLocation($order->getAgent()->getUser()->getUpozilla());
                $order->setOrderType(Order::ORDER_TYPE_CHICK);
                $order->setCreatedAt(new \DateTime($orderDate));
                $orderIncentiveFlag->setOrder($order);
                $this->orderRepository()->createChick($order);

                /** @var OrderItem $item */
                foreach ($order->getOrderItems() as $item){
                    /*$mrpPrice = $this->getDoctrine()->getRepository('RbsCoreBundle:ItemPrice')->getCurrentMrpPrice(
                        $item->getItem(), $order->getDepo()->getLocation()
                    );
                    $item->setMrpPrice($mrpPrice);*/
                    $item->setPreviousQuantity($item->getQuantity());
                    $item->setBonusQuantity((int) $item->getQuantity()/$item->getItem()->getPacketWeight());

                    $this->getDoctrine()->getRepository('RbsSalesBundle:OrderItem')->update($item);

                    if($delivery){
                        $deliveryItem = new DeliveryItem();
                        $deliveryItem->setOrder($order);
                        $deliveryItem->setOrderItem($item);
                        $deliveryItem->setDelivery($delivery);
                        $deliveryItem->setQty($item->getQuantity());
                        $this->getDoctrine()->getRepository('RbsSalesBundle:DeliveryItem')->create($deliveryItem);

                    }
                }

                if($delivery){
//                    $vehicle = $delivery->getVehicles()[0];
                    $delivery->addOrder($order);
                    $this->getDoctrine()->getRepository('RbsSalesBundle:Delivery')->update($delivery);

                    $this->getDoctrine()->getRepository('RbsSalesBundle:Payment')->createDeliveredProductValueForSingleChickOrder($delivery, $order);

                    $order->setOrderState('COMPLETE');
                    $order->setPaymentState('PENDING');
                    $order->setDeliveryState('SHIPPED');

                    $this->getDoctrine()->getRepository('RbsSalesBundle:Order')->onlyUpdate($order);

                    /** @var Vehicle $vehicle */
                    foreach ($delivery->getVehicles() as $vehicle){
                        $vehicle->setOrderText($this->setOrderText($delivery->getOrders()));
                        $this->getDoctrine()->getRepository('RbsSalesBundle:Vehicle')->update($vehicle);
                    }
                }

                $msg = "Dear Agent, Your order no ".$order->getId()." is in process for confirmation.";

                $part1s = str_split($msg, $split_length = 160);
                foreach($part1s as $part){
                    $smsSender = $this->get('rbs_erp.sales.service.smssender');
                    $smsSender->agentBankInfoSmsAction($part, $order->getAgent()->getUser()->getProfile()->getCellphoneForChick());
                }

                $this->getDoctrine()->getRepository('RbsSalesBundle:OrderIncentiveFlag')->create($orderIncentiveFlag);

                $em->getRepository('RbsSalesBundle:DailyDepotStock')->addStockToOnHold($requestDate, $order, $order->getDepo(), array());

                $this->flashMessage('success', 'Order Created Successfully');

                return $this->redirect($this->generateUrl('chick_orders_home'));
            }
            a:
            return $this->redirect($this->generateUrl('chick_order_create_new'));
        }
        $priceModifyAccess = $this->getDoctrine()->getRepository("RbsCoreBundle:CoreSettings")->findOneBy(array('settingType'=>CoreSettings::SETTING_TYPE_CHICK,'slug'=>'item-price-modify-access-chick'));
//        $deliveries = $this->getDoctrine()->getRepository('RbsSalesBundle:Delivery')->findBy();

        return array(
            'form' => $form->createView(),
            'priceModifyAccess' => $priceModifyAccess,
        );
    }
    private function setOrderText($orders){
        $returnText = '';
        $key = 0;
        /** @var Order $order */
        foreach ($orders as $order){
            $returnText .= $order->getId();
            if (count($orders)!=$key+1) $returnText .= ", ";

            $key++;
        }
        return $returnText;
    }


    /**
     * @Route("/chick/order/edit/{id}", name="chick_order_edit", options={"expose"=true})
     * @Template("RbsSalesBundle:ChickOrder:edit-chick-order.html.twig")
     * @param Request $request
     * @param Order $order
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @JMS\Secure(roles="ROLE_DEPO_USER, ROLE_CHICK_ORDER_MANAGE")
     */

    public function chickOrderEditAction(Request $request, Order $order)
    {
        $agentId= ($order->getAgent())? $order->getAgent()->getId(): null;
        $allRequest = $request->request->get('order');
        if (in_array($order->getOrderState(), array(ORDER::ORDER_STATE_CANCEL))) {
            $this->flashMessage('error', 'Invalid Operation');
            return $this->redirectToRoute('orders_home');
        }

        $form = $this->createForm(new ChickOrderEditForm($this->getDoctrine()->getManager(), $agentId), $order);
        //$form->remove('agent');
//        $form->remove('created_at');
        $em = $this->getDoctrine()->getManager();

        $depoAttr = Order::ORDER_STATE_COMPLETE == $order->getOrderState() ? array('style'=>'pointer-events: none; opacity: 0.6;') : array();
        $agentAttr =  array(''=>'');
        if ('POST' === $request->getMethod()) {

            $stockRepo = $em->getRepository('RbsSalesBundle:Stock');
            $prevDepo = clone $order->getDepo();

//            $prevOrderItems = $stockRepo->extractOrderItemQuantity($order);
            $form->handleRequest($request);

            $depo = $order->getDepo() ? $order->getDepo() : $prevDepo;

            /** @var OrderItem $item */
            foreach ($order->getOrderItems() as $item){
                if ($item->getQuantity() < 1) {
                    $this->flashMessage('error', 'Invalid Item Quantity');
                    goto a;
                }

                /*$price = $this->getDoctrine()->getRepository('RbsCoreBundle:ItemPrice')->getCurrentPrice(
                    $item->getItem(), $depo->getLocation()
                );*/
                if ($item->getPrice() < 1) {
                    $this->flashMessage('error', 'Invalid Item Price');
                    goto a;
                }
            }

            if ($form->isValid()) {
                $prevOrderItems = $request->request->get('added_qty');
                $currentTime = date('H:i:s',strtotime('now'));
                $requestDate = isset($allRequest['created_at'])?date('Y-m-d',strtotime($allRequest['created_at'])):date('Y-m-d',strtotime('now'));
                $orderDate = $requestDate.' '.$currentTime;
                $em = $this->getDoctrine()->getManager();
                $order->setLocation($order->getAgent()->getUser()->getUpozilla());
//                $order->setDepo($depo);
                $order->setCreatedAt(new \DateTime($orderDate));

                $em->getRepository('RbsSalesBundle:Order')->onlyUpdate($order);

                /** @var OrderItem $item */
                foreach ($order->getOrderItems() as $item){
                    /*$mrpPrice = $this->getDoctrine()->getRepository('RbsCoreBundle:ItemPrice')->getCurrentMrpPrice(
                        $item->getItem(), $depo->getLocation()
                    );
                    $item->setMrpPrice($mrpPrice);*/
                    $item->setPreviousQuantity($item->getQuantity());
                    $item->setBonusQuantity((int) $item->getQuantity()/$item->getItem()->getPacketWeight());

                    $this->getDoctrine()->getRepository('RbsSalesBundle:OrderItem')->update($item);

                    if($order->getDeliveries()){
                        foreach ($order->getDeliveries() as $delivery){

                            $deliveryItem = $this->getDoctrine()->getRepository('RbsSalesBundle:DeliveryItem')->findOneBy(
                                array('delivery'=>$delivery, 'order'=>$order, 'orderItem'=>$item)
                            );
                            $deliveryItem->setQty($item->getQuantity());
                            $this->getDoctrine()->getRepository('RbsSalesBundle:DeliveryItem')->update($deliveryItem);

                            $this->getDoctrine()->getRepository('RbsSalesBundle:Payment')->updateDeliveredProductValueForSingleChickOrder($delivery, $order);

                        }
                    }


                }

                $em->getRepository('RbsSalesBundle:DailyDepotStock')->addStockToOnHold($requestDate, $order, $depo, $prevOrderItems);

                $this->flashMessage('success', 'Order Updated Successfully');

                return $this->redirect($this->generateUrl('chick_orders_home'));
            }
            a:
            return $this->redirect($this->generateUrl('chick_order_edit', array('id' => $order->getId())));
        }
        $priceModifyAccess = $this->getDoctrine()->getRepository("RbsCoreBundle:CoreSettings")->findOneBy(array('settingType'=>CoreSettings::SETTING_TYPE_CHICK,'slug'=>'item-price-modify-access-chick'));
        $deliveries = $order->getDeliveries();
        return array(
            'form' => $form->createView(),
            'order' => $order,
            'depoAttr' => $depoAttr,
            'agentAttr' => $agentAttr,
            'priceModifyAccess' => $priceModifyAccess,
            'deliveries' => $deliveries,
        );
    }


    /**
     * @Route("/chick/order/details/{id}", name="chick_order_details", options={"expose"=true})
     * @param Order $order
     * @return \Symfony\Component\HttpFoundation\Response
     * @JMS\Secure(roles="ROLE_CHICK_ORDER_MANAGE")
     */
    public function detailsAction(Order $order)
    {
        $this->checkViewOrderAccess($order);
        $deliveryItems = $this->getDoctrine()->getRepository('RbsSalesBundle:DeliveryItem')->findBy(array(
            'order' => $order->getId(),
        ));

        $deliveredItems = $this->getDoctrine()->getRepository('RbsSalesBundle:DeliveryItem')->getDeliveredItems($order);
        $auditLogs = $this->getDoctrine()->getRepository('RbsCoreBundle:AuditLog')->getByTypeOrObjectId(array(
            'order.approved', 'order.verified', 'order.hold', 'order.canceled', 'payment.approved', 'payment.over.credit.approved'), $order->getId());

        return $this->render('RbsSalesBundle:Order:details.html.twig', array(
            'order' => $order,
            'deliveryItems' => $deliveryItems,
            'deliveredItems' => $deliveredItems,
            'auditLogs' => $auditLogs,
        ));
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
        $itemMrpPrice = $request->request->get('orderItemMrpPrice');
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
                    $orderItem->setPreviousQuantity((int) $orderItemValue);
                    $orderItem->setBonusQuantity((int) $orderItemValue/$orderItem->getItem()->getPacketWeight());

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
                ),
                'status'=>1
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
        date_default_timezone_set("Asia/Dhaka");
        set_time_limit(0);
        ini_set('memory_limit','1024M');
        $em = $this->getDoctrine()->getManager();
        $date = $request->query->get('order_date') ? date('Y-m-d H:i:s', strtotime($request->query->get('order_date'))) : date('Y-m-d H:i:s', time());

        $currentTime = date('H:i:s',strtotime('now'));
        $requestDate = $request->query->get('order_date')?date('Y-m-d',strtotime($request->query->get('order_date'))):date('Y-m-d',strtotime('now'));
        $orderDate = $requestDate.' '.$currentTime;

        $areaRegionIds = array();
        $areaIds = array();
        foreach($depo->getAreas() as $area) {
            $areaRegionIds[] = $area->getParentId();
            $areaIds[] = $area->getId();
        }
        $locationsRegions = $this->getDoctrine()->getRepository('RbsCoreBundle:Location')->getRegionsForChickByDepotAreas($areaRegionIds);

        $agentLists = $this->getDoctrine()->getRepository('RbsSalesBundle:Agent')->getChickAgentsListByDepotAreas($areaIds);

        $dailyStocks = $this->getDoctrine()->getRepository('RbsSalesBundle:DailyDepotStock')->getDailyStock($date);

        $chickItems = $this->getChickItems();
        $depots = $this->getDepots();

        $depotId = $depo->getId();
        $areas = $depo->getAreas();
        $areaId='';

        foreach ($areas as $key=>$area){
            $areaId .= $area->getId();
            if (count($areas)!=$key+1) $areaId .= ",";
        }

        if ($request->query->get('order_date')){
               if(!$this->getChickTempOrdersByDateAgentDepot($date,$depo)){
                   $sql ="INSERT INTO sales_orders_chick_temp
    (`agent_id`, `depo_id`,`order_type`, `location_id`, `created_at`)
SELECT a.id ,{$depotId},'CHICK',u.upozilla_id as upId, '{$orderDate}' FROM sales_agents AS a 
INNER JOIN user_users u ON a.user_id = u.id 
INNER JOIN core_locations l ON u.zilla_id = l.id 
WHERE a.agent_type = 'CHICK' AND u.deleted_at IS NULL AND l.id IN ({$areaId})";

                   $qb = $em->getConnection()->prepare($sql);
                   $qb->execute();

                  $addedTempOrders = $this->getChickTempOrdersByDateAgentDepot($date,$depo);

                  foreach ($addedTempOrders as $order){

                      $orderObj = $this->getDoctrine()->getRepository('RbsSalesBundle:OrderChickTemp')->find($order['id']);

                      $locationDist = $orderObj->getAgent()->getUser()->getZilla()->getId();
                      $sqlChild ="INSERT INTO sales_order_items_chick_temp
    (`order_id`, `item_id`,`quantity`, `price`, `mrp_price`, `total_amount`)
SELECT {$order['id']}, core_items.id, 0, (SELECT core_item_price.price FROM `core_item_price` WHERE `item_id` = core_items.id AND `location_id` = {$locationDist} AND `is_active` = 1) as price, (SELECT core_item_price.mrp_price FROM `core_item_price` WHERE `item_id` = core_items.id AND `location_id` = {$locationDist} AND `is_active` = 1) AS mrpPrice,0 FROM `core_items` WHERE `item_types` = 3 AND `status`=1";

                      $qb = $em->getConnection()->prepare($sqlChild);
                      $qb->execute();

//                      $this->insertOrderItem($orderObj, $chickItems);
                  }
               }
            $addedData = $this->getTempChickOrdersByDateDepot($date, $depo);

        }

        return $this->render('RbsSalesBundle:ChickOrder:add-check-order.html.twig', array(
                'date'            => $request->query->get('order_date'),
                'orderDate'       => $date,
                'agentsList'      => $agentLists,
                'chickItems'      => $chickItems,
                'depot'           => $depo,
                'ordersTemp'      => $addedData,
                'dailyStocks'     => $dailyStocks,
                'locationsRegions'=> $locationsRegions,
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
            $date = $data['order_date'] ?  $data['order_date'] : date('Y-m-d', time());
            if ($date){
                $onlyDate = $date->format('Y-m-d');

                $addedData = $this->getFinalChickOrdersByDateDepot($onlyDate, $data['depot']);

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
               $item, $order->getAgent()->getUser()->getZilla()
           );
           $mrpPrice = $this->getDoctrine()->getRepository('RbsCoreBundle:ItemPrice')->getCurrentMrpPrice(
               $item, $order->getAgent()->getUser()->getZilla()
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

    private function getChickTempOrdersByDateAgentDepot($date, $depot) {
        $date = date('Y-m-d', strtotime($date));
        $repo = $this->getDoctrine()->getRepository('RbsSalesBundle:OrderChickTemp');
        $qb = $repo->createQueryBuilder('o');
        $qb->select('o.id');

        $qb->where($qb->expr()->between('o.createdAt', ':start', ':end'));
        $qb->setParameters(array('start' => $date . ' 00:00:00', 'end' => $date . ' 23:59:59'));

        $qb->andWhere('o.depo = :depo');
        $qb->setParameter('depo', $depot);

        $result = $qb->getQuery()->getResult();

        return $result;
    }

    private function getTempChickOrdersByDateDepot($date, $depot) {
        $date= date('Y-m-d', strtotime($date));
        $repo = $this->getDoctrine()->getRepository('RbsSalesBundle:OrderItemChickTemp');
        $qb = $repo->createQueryBuilder('oi');
        $qb->select('o.id as oId, oi.id as oiId, oi.quantity as quantity, a.id as aId, i.id as iId, d.id as dId');
        $qb->join('oi.order', 'o');
        $qb->join('oi.item','i');
        $qb->join('o.agent','a');
        $qb->join('o.depo','d');

        $qb->where($qb->expr()->between('o.createdAt', ':start', ':end'));
        $qb->setParameters(array('start' => $date . ' 00:00:00', 'end' => $date . ' 23:59:59'));

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
        $qb->setParameters(array('start' => $date . ' 00:00:00' , 'end' => $date . ' 23:59:59' ));

        $qb->andWhere('o.orderState=:orderSate');
        $qb->setParameter('orderSate', Order::ORDER_STATE_PROCESSING);
        $qb->andWhere('o.deliveryState=:deliveryState');
        $qb->setParameter('deliveryState', Order::DELIVERY_STATE_READY);


        $qb->andWhere('o.depo = :depot');
        $qb->setParameter('depot', $depot);

        $qb->groupBy('d.id, a.id, i.id');

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
        if(($stock->getRemainingQuantity()+$previousOrderItemQuantity)>=$itemQuantity){
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
            'stockRemainingQuantity'     => $stock->getRemainingQuantity(),
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
            $orderItem->setBonusQuantity($itemQuantity/$orderItem->getItem()->getPacketWeight());
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
                $order->setTotalApprovedAmount($orderChickTemp->getTotalAmount());
                $order->setLocation($orderChickTemp->getAgent()->getUser()->getUpozilla());
                $order->setDeliveryState(Order::DELIVERY_STATE_READY);
                $order->setOrderState(Order::ORDER_STATE_PROCESSING);
                $order->setPaymentState(Order::PAYMENT_STATE_PENDING);
                $order->setOrderType(Order::ORDER_TYPE_CHICK);
                $order->setOrderVia('SYSTEM');
                $order->setCreatedAt($orderChickTemp->getCreatedAt());

                $this->getDoctrine()->getManager()->persist($order);

                /** @var OrderItemChickTemp $orderChickItemTemp */
                foreach ($orderChickTemp->getOrderItems() as $orderChickItemTemp){
                    if($orderChickItemTemp->getQuantity()>0){
                        $orderItem = new OrderItem();
                        $orderItem->setQuantity($orderChickItemTemp->getQuantity());
                        $orderItem->setPreviousQuantity($orderChickItemTemp->getQuantity());
                        $orderItem->setPrice($orderChickItemTemp->getPrice());
                        $orderItem->setMrpPrice($orderChickItemTemp->getMrpPrice());
                        $orderItem->setTotalAmount($orderChickItemTemp->getTotalAmount());
                        $orderItem->setItem($orderChickItemTemp->getItem());
                        $orderItem->setBonusQuantity($orderChickItemTemp->getQuantity()/$orderChickItemTemp->getItem()->getPacketWeight());
                        $orderItem->setOrder($order);
                        $this->getDoctrine()->getManager()->persist($orderItem);
                    }

                    $orderChickTemp->removeOrderItem($orderChickItemTemp);

                }
                $this->getDoctrine()->getManager()->flush();
                $data[]= $order->getId();
                $this->smsSend($order);
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

    private function smsSend(Order $order)
    {
//            $orderItems=$this->getDoctrine()->getRepository('RbsSalesBundle:OrderItem')->findBy(array('order'=>$order));

        $msg = "Dear Agent, Your order no ".$order->getId()." is in process for confirmation.";

        $part1s = str_split($msg, $split_length = 160);
        foreach($part1s as $part){
            $smsSender = $this->get('rbs_erp.sales.service.smssender');
            $smsSender->agentBankInfoSmsAction($part, $order->getAgent()->getUser()->getProfile()->getCellphoneForChick());
        }
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
     * @Route("/chick/order/cancel/{id}", name="chick_order_cancel", options={"expose"=true})
     * @param Order $order
     * @return \Symfony\Component\HttpFoundation\Response
     * @JMS\Secure(roles="ROLE_CHICK_ORDER_MANAGE")
     */
    public function orderCancelAction(Order $order)
    {

        $this->orderRepository()->cancelChickOrder($order);
        $this->dispatchApproveProcessEvent('order.canceled', $order);
        $this->flashMessage('success', 'Order Cancelled Successfully');
        return $this->redirect($this->generateUrl('chick_orders_home'));
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

    protected function checkViewOrderAccess(Order $order)
    {
        if ($this->isGranted('ROLE_AGENT') && $order->getAgent()->getUser()->getId() != $this->getUser()->getId()) {
            throw new AccessDeniedException('Access Denied');
        }

        if ($this->isGranted('ROLE_AGENT')) {
            $isOwnAgent = $this->agentRepository()->findOneBy(array(
                'agent' => $this->getUser(),
                'id' => $order->getAgent()->getId(),
            ));
            if (!$isOwnAgent) {
                throw new AccessDeniedException('Access Denied');
            }
        }
    }


    /**
     * remove order item and daily depot stock update by item and depot using ajax
     * @Route("remove_order_item_and_stock_update_by_item_depot/{order}/{item}", name="remove_order_item_and_stock_update_by_item_depot_ajax", options={"expose"=true})
     * @param Request $request
     * @return Response
     * @JMS\Secure(roles="ROLE_CHICK_ORDER_MANAGE")
     */
    public function removeOrderItemAndStockUpdateByItemDepotAjax(Request $request, Order $order, Item $item)
    {
        $orderCreatedDate = $request->query->get('order_date') ? date('Y-m-d', strtotime($request->query->get('order_date'))) : date('Y-m-d', time());

        $em = $this->getDoctrine()->getManager();
        /* @var $orderItem OrderItem */
        $orderItem = $this->getDoctrine()->getRepository('RbsSalesBundle:OrderItem')->findOneBy(array('order'=>$order, 'item'=>$item));

        if($orderItem){

            $em = $this->getDoctrine()->getManager();
            $response = "invalid";
            if (!$orderItem) {
                throw $this->createNotFoundException('Unable to find Particular entity.');
            }
            try {

                if($order->getDeliveries()){

                    $deliveryItems = $this->getDoctrine()->getRepository('RbsSalesBundle:DeliveryItem')->findBy(array('order'=>$order, 'orderItem'=>$orderItem));

                    if($deliveryItems){
                        /* @var $deliveryItem DeliveryItem */
                        foreach ($deliveryItems as $deliveryItem){
                            $em->getRepository('RbsSalesBundle:DeliveryItem')->delete($deliveryItem);
                        }
                    }
                }

                $dailyDepotStockArray = $em->getRepository('RbsSalesBundle:DailyDepotStock')->getDailyStockByDateItemDepot($orderCreatedDate, $item, $order->getDepo() );

                if($dailyDepotStockArray){
                    $dailyDepotStock = $em->getRepository('RbsSalesBundle:DailyDepotStock')->find($dailyDepotStockArray['id']);
                    $dailyDepotStock->setOnHold($dailyDepotStock->getOnHold()-$orderItem->getQuantity());
                    $em->getRepository('RbsSalesBundle:DailyDepotStock')->update($dailyDepotStock);
                }

                $order->removeOrderItem($orderItem);
                $em->getRepository('RbsSalesBundle:OrderItem')->delete($orderItem);

                $em->getRepository('RbsSalesBundle:Order')->onlyUpdate($order);
                $em->flush();
                $response = array(
                    'status'     => 200,
                    'message'     => 'Order item remove and stock update successfully',
                );

            } catch (\Exception $e) {
                $this->get('session')->getFlashBag()->add(
                    'notice', 'Please contact system administrator further notification.'
                );
            }
            return new JsonResponse($response);
        }

        $response = array(
            'status'     => 200,
            'message'     => 'Order item remove and stock update successfully',
        );

        return new JsonResponse($response);
    }
}