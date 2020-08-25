<?php

namespace Rbs\Bundle\SalesBundle\Controller;

use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use JMS\SecurityExtraBundle\Annotation as JMS;
use Rbs\Bundle\CoreBundle\Entity\ItemType;
use Rbs\Bundle\SalesBundle\Entity\Delivery;
use Rbs\Bundle\SalesBundle\Entity\Order;
use Rbs\Bundle\SalesBundle\Entity\OrderItem;
use Rbs\Bundle\SalesBundle\Entity\OrderItemAmendmentHistory;
use Rbs\Bundle\SalesBundle\Event\DeliveryEvent;
use Rbs\Bundle\SalesBundle\Form\Type\DeliveryAddForm;
use Rbs\Bundle\SalesBundle\Form\Type\DeliveryForm;
use Rbs\Bundle\SalesBundle\Repository\OrderItemAmendmentHistoryRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Delivery Controller.
 *
 */
class DeliveryController extends BaseController
{
    /**
     * @Route("/challan/add", name="deliveries_home")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @JMS\Secure(roles="ROLE_DELIVERY_MANAGE")
     */
    public function indexAction(Request $request)
    {
        $datatable = $this->get('rbs_erp.sales.datatable.delivery');
        $datatable->buildDatatable();

        return $this->render('RbsSalesBundle:Delivery:index.html.twig', array(
            'datatable' => $datatable,
        ));
    }

    /**
     * @Route("/delivery_list_ajax", name="delivery_list_ajax", options={"expose"=true})
     * @Method("GET")
     * @JMS\Secure(roles="ROLE_DELIVERY_MANAGE")
     * @param Request $request
     * @return Response
     */
    public function listAjaxAction(Request $request)
    {
        $datatable = $this->get('rbs_erp.sales.datatable.delivery');
        $datatable->buildDatatable();

        $dateFilter = $request->query->get('columns[1][search][value]', null, true);
        $orderFilter = $request->query->get('columns')[0]['search']['value'];

        $columns = $request->query->get('columns');
        $columns[0]['search']['value'] = '';
        $columns[1]['search']['value'] = '';
        $request->query->set('columns', $columns);

        $query = $this->get('sg_datatables.query')->getQueryFrom($datatable);

        /** @var QueryBuilder $qb */
        $function = function($qb) use ($dateFilter, $orderFilter)
        {
            $qb->join('sales_deliveries.depo', 'd');
            $qb->join('sales_deliveries.orders', 'o');
            $qb->join('d.users', 'u');
            $qb->andWhere('u =:user');
            $qb->andWhere('sales_deliveries.shipped = 0');
            $qb->andWhere('orders.deliveryState IN (:READY) OR orders.deliveryState IN (:PARTIALLY_SHIPPED)');
            $qb->setParameters(array('user'=>$this->getUser(), 'READY'=>Order::DELIVERY_STATE_READY, 'PARTIALLY_SHIPPED'=>Order::DELIVERY_STATE_PARTIALLY_SHIPPED));

            if ($orderFilter) {
                $qb->andWhere('o.id =:orders');
                $qb->setParameter('orders', $orderFilter);
            }
            if ($dateFilter) {
                $qb->andWhere('orders.createdAt BETWEEN :fromDate AND :toDate')
                    ->setParameter('fromDate', date('Y-m-d 00:00:00', strtotime($dateFilter)))
                    ->setParameter('toDate', date('Y-m-d 23:59:59', strtotime($dateFilter)));
            }
        };
        $query->addWhereAll($function);

        return $query->getResponse();
    }

    /**
     * @Route("/delivery/{id}", name="delivery_view", options={"expose"=true})
     * @param Delivery $delivery
     * @return \Symfony\Component\HttpFoundation\Response
     * @JMS\Secure(roles="ROLE_DELIVERY_MANAGE")
     */
    public function view(Delivery $delivery)
    {
        $items = $this->getFeedItems();
        $stockRepo = $this->getDoctrine()->getRepository('RbsSalesBundle:Stock');
        $partialItems = $this->getDoctrine()->getRepository('RbsSalesBundle:DeliveryItem')->getPartialDeliveredItems($delivery);
        $isAvailable=array();
        /** @var Order $order */
        foreach ($delivery->getOrders() as $order){
            /** @var OrderItem $item */
            foreach ($order->getOrderItems() as $item) {
                $stockItem = $stockRepo->findOneBy(
                    array('item' => $item->getItem()->getId(), 'depo' => $order->getDepo()->getId())
                );
                $isAvailable[$order->getId()][$item->getId()] = $stockItem && $stockItem->isStockAvailableForDeliverySet($item->getQuantity());
            }
        }
        return $this->render('RbsSalesBundle:Delivery:view.html.twig', array(
            'delivery'      => $delivery,
            'partialItems'  => $partialItems,
            'items'  => $items,
            'isAvailable'  => $isAvailable,
        ));
    }

    private function getFeedItems() {
        $em = $this->getDoctrine()->getManager();

        return $em->getRepository('RbsCoreBundle:Item')->getFeedItems();
    }

    /**
     * @Route("/update-delivery/{id}", name="update_delivery", options={"expose"=true})
     * @Template("RbsSalesBundle:Delivery:_edit.html.twig")
     * @param Request $request
     * @param Delivery $delivery
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     * @JMS\Secure(roles="ROLE_ORDER_EDIT")
     */
    public function updateDeliveryAction(Request $request, Delivery $delivery)
    {
        $form = $this->createForm(
            new DeliveryForm(), $delivery, array(
                'action' => $this->generateUrl('update_delivery', array('id' => $delivery->getId())),
                'attr'   => array(
                    'novalidate' => 'novalidate',
                ),
            )
        );

        if ('POST' === $request->getMethod()) {

            $form->handleRequest($request);

            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();

                $em->persist($delivery);
                $em->flush();

                $this->flashMessage('success', 'Delivery Information Update Successfully');

                return $this->redirect($this->generateUrl('order_details', array('id' => $delivery->getOrders()->getId())));
            }
        }

        return array(
            'form' => $form->createView(),
        );
    }
    
    /**
     * @Route("/delivery-save/{id}", name="delivery_save", options={"expose"=true})
     * @param Request $request
     * @param Delivery $delivery
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     * @JMS\Secure(roles="ROLE_DELIVERY_MANAGE")
     */
    public function deliverySetAction(Request $request, Delivery $delivery)
    {
        $data = $this->getDoctrine()->getRepository('RbsSalesBundle:Delivery')->save($delivery, $this->get('request')->request->all());
        $this->getDoctrine()->getRepository('RbsSalesBundle:Order')->updateDeliveryState($data['orders']);
        $this->getDoctrine()->getRepository('RbsSalesBundle:Stock')->removeStock($delivery);
        $this->getDoctrine()->getRepository('RbsSalesBundle:Payment')->createDeliveredProductValue($delivery);

        if (!empty($this->get('request')->request->get('checked-vehicles'))) {
            foreach ($this->get('request')->request->get('checked-vehicles') as $vehicleId => $vehicle) {
                $vehicleObj = $this->getDoctrine()->getRepository('RbsSalesBundle:Vehicle')->find($vehicleId);
                $vehicleObj->setShipped(true);
                $this->getDoctrine()->getRepository('RbsSalesBundle:Vehicle')->update($vehicleObj);
            }
        }

        $this->dispatch('delivery.delivered', new DeliveryEvent($delivery));

        $this->flashMessage('success', 'Delivery Completed Successfully');

        return $this->redirect($this->generateUrl('vehicle_info_load_list'));
    }

    /**
     * @Route("/order/item/add", name="order_item_add_ajax", options={"expose"=true})
     * @param Request $request
     * @param Delivery $delivery
     * @return array|\Symfony\Component\HttpFoundation\JsonResponse
     * @JMS\Secure(roles="ROLE_DELIVERY_MANAGE")
     */
    public function orderItemAddAction(Request $request)
    {
        $returnData = array();
        $orderId = $request->request->get('orderId');
//        $orderId = 23893;
        $itemId = $request->request->get('itemId');
//        $itemId = 49;
        $amendmentItemId = $request->request->get('amendmentItemId');
//        $amendmentItemId = 6;
        $itemQty = $request->request->get('itemQty');

        $amendmentItemQty = $request->request->get('amendmentItemQty');
//        $itemQty = 200;
        $deliveryId = $request->request->get('deliveryId');
//        $deliveryId = 22807;


        if($itemQty<=0){
            return new JsonResponse(
                array('status'=> 'error','message'=>"Quantity more than 0")
            );
        }


        $order = $this->getDoctrine()->getRepository('RbsSalesBundle:Order')->find($orderId);
        $item = $this->getDoctrine()->getRepository('RbsCoreBundle:Item')->find($itemId);
        $amendmentItem = $this->getDoctrine()->getRepository('RbsCoreBundle:Item')->find($amendmentItemId);
        $delivery = $this->getDoctrine()->getRepository('RbsSalesBundle:Delivery')->find($deliveryId);

        $amendmentOrderItem = $this->getDoctrine()->getRepository('RbsSalesBundle:OrderItem')->findOneBy(array('order'=>$order, 'item'=>$amendmentItem));

        $amendedHistory = $this->getDoctrine()->getRepository('RbsSalesBundle:OrderItemAmendmentHistory')->findOneBy(
            array('order' => $order, 'item' => $item, 'delivery'=>$delivery, 'amendmentItem'=>$amendmentItem)
        );

        $partialItems = $this->getDoctrine()->getRepository('RbsSalesBundle:DeliveryItem')->getPartialDeliveredItems($delivery);
        $partialDeliveryItemQty=0;
        if ($partialItems){
            $partialDeliveryItemQty = isset($partialItems[$order->getId()][$amendmentOrderItem->getId()]['delivered'])?$partialItems[$order->getId()][$amendmentOrderItem->getId()]['delivered']:0;
        }

        $amendedQty = 0;
        if ($amendedHistory){
            $amendedQty = $amendedHistory->getAmendmentQuantity();
        }

        if(($amendmentOrderItem->getQuantity()+$amendedQty-$partialDeliveryItemQty)<$amendmentItemQty){
            return new JsonResponse(
                array('status'=> 'error','message'=>"Remaining quantity cross")
            );
        }

        $orderItemAmendmentHistory = new OrderItemAmendmentHistory();
        $exHistoryQty = $itemQty;
        $exHistoryAmendmentQty = $amendmentItemQty;
        if ($amendedHistory){
            $orderItemAmendmentHistory = $amendedHistory;
            $exHistoryQty = $amendedHistory->getQuantity()+$itemQty;
            $exHistoryAmendmentQty = $amendedHistory->getAmendmentQuantity()+$amendmentItemQty;
        }

        $orderItemAmendmentHistory->setOrder($order);
        $orderItemAmendmentHistory->setItem($item);
        $orderItemAmendmentHistory->setDelivery($delivery);
        $orderItemAmendmentHistory->setQuantity($exHistoryQty);
        $orderItemAmendmentHistory->setAmendmentQuantity($exHistoryAmendmentQty);
        $orderItemAmendmentHistory->setAmendmentItem($amendmentItem);
        $this->getDoctrine()->getRepository('RbsSalesBundle:OrderItemAmendmentHistory')->update($orderItemAmendmentHistory);





        $price = $this->getDoctrine()->getRepository('RbsCoreBundle:ItemPrice')->getCurrentPrice(
            $item, $order->getAgent()->getUser()->getZilla()
        );
        $exOrderItem = array();
        /** @var OrderItem $orderItem */
        foreach ($order->getOrderItems() as $orderItem){
          $exOrderItem[]=$orderItem->getItem()->getId();
        }
        $stockRepo = $this->getDoctrine()->getRepository('RbsSalesBundle:Stock');
        $stockItem = $stockRepo->findOneBy(
            array('item' => $item, 'depo' => $order->getDepo())
        );

        if (in_array($item->getId(), $exOrderItem)){

            $item->isAvailable = $stockItem && $stockItem->isStockAvailableForDeliverySet($itemQty);
            if($item->isAvailable==false){
                return new JsonResponse(
                    array('status'=> 'error','message'=>"Item stock not available")
                );
            }
          $orderItem = $this->getDoctrine()->getRepository('RbsSalesBundle:OrderItem')->findOneBy(array('order'=>$order,'item'=>$item));

            $orderItem->setQuantity( $orderItem->getQuantity() + $itemQty);

          if(($amendmentOrderItem->getPrice()* $amendmentItemQty) < ($price*$itemQty)){
              return new JsonResponse(
                  array('status'=> 'error','message'=>"Order clearance amount cross.",$order->getTotalApprovedAmount(),(($order->getTotalAmount()-$orderItem->getTotalAmount()) + ($price*$itemQty) ))
              );
          }

          $stock = $this->getDoctrine()->getRepository('RbsSalesBundle:Stock')->findOneBy(
                array(
                    'item' => $orderItem->getItem(),
                    'depo' => $order->getDepo(),
                )
            );
            $stock->setOnHold($stock->getOnHold() - $orderItem->getQuantity());
            $this->getDoctrine()->getRepository('RbsSalesBundle:Stock')->update($stock);

//          $this->getDoctrine()->getRepository('RbsSalesBundle:Stock')->addStockToOnHold($order, $depo);
            $returnData['type']='old';
        }else{

            $item->isAvailable = $stockItem && $stockItem->isStockAvailable($itemQty);
            if($item->isAvailable==false){
                return new JsonResponse(
                    array('status'=> 'error','message'=>"Item stock not available.")
                );
            }

            $orderItem = new OrderItem();

            $orderItem->setQuantity($itemQty);

            if(($amendmentOrderItem->getPrice()* $amendmentItemQty) < ($price*$itemQty)){
                return new JsonResponse(
                    array('status'=> 'error','message'=>"Order clearance amount cross.")
                );
            }
            $returnData['type']='new';
        }

        if(($amendmentOrderItem->getQuantity()+$amendedQty-$partialDeliveryItemQty)>=$amendmentItemQty){
            $amendmentOrderItem->setQuantity($amendmentOrderItem->getQuantity()-$amendmentItemQty);
            $amendmentOrderItem->calculateTotalAmount(true);
            $this->getDoctrine()->getRepository("RbsSalesBundle:OrderItem")->update($amendmentOrderItem);
        }else{
            return new JsonResponse(
                array('status'=> 'error','message'=>"Remaining quantity cross")
            );
        }


        $orderItem->setItem($item);

        $orderItem->setPrice($price);
        $orderItem->setTotalAmount($orderItem->calculateTotalAmount());
        $orderItem->setOrder($order);

        /*$this->getDoctrine()->getEntityManager()->persist($orderItem);
        $this->getDoctrine()->getEntityManager()->flush();*/

        $order->addOrderItem($orderItem);

        $this->getDoctrine()->getRepository("RbsSalesBundle:OrderItem")->update($orderItem);

        $this->getDoctrine()->getRepository("RbsSalesBundle:Order")->onlyUpdate($order);
//        $order->setTotalAmount($order->getItemsTotalAmount());


        $stock = $this->getDoctrine()->getRepository('RbsSalesBundle:Stock')->findOneBy(
            array(
                'item' => $orderItem->getItem(),
                'depo' => $order->getDepo(),
            )
        );
        $stock->setOnHold($stock->getOnHold() + $orderItem->getQuantity());
        $this->getDoctrine()->getRepository('RbsSalesBundle:Stock')->update($stock);

        $data=array(
            'status'=> 'success',
            'message'=>'Item has been added.',
            'orderId'=>$orderItem->getOrder()->getId(),
            'orderItemId'=>$orderItem->getId(),
            'itemName'=>$orderItem->getItem()->getName(),
            'itemQty'=>$orderItem->getQuantity(),
            'totalAmount'=>$order->getTotalAmount(),
            'orderItemCount'=>count($order->getOrderItems()),
        );



        $return= array_merge($returnData, $data);

        return new JsonResponse($return);

    }


    /**
     * @Route("/delivery-update/{id}", name="delivery_update", options={"expose"=true})
     * @param Request $request
     * @param Delivery $delivery
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     * @JMS\Secure(roles="ROLE_DELIVERY_MANAGE")
     */
    public function deliveryUpdateAction(Request $request, Delivery $delivery)
    {
//        var_dump($request->request->get('deliveryItemQty'));die;
        $this->getDoctrine()->getRepository('RbsSalesBundle:Stock')->updateStockToOnHold($request->request->get('deliveryItemQty'));
        $data = $this->getDoctrine()->getRepository('RbsSalesBundle:Delivery')->deliveryUpdate($request->request->get('deliveryItemQty'));
        $this->getDoctrine()->getRepository('RbsSalesBundle:Order')->updateDeliveryState($data['orders']);
        $this->getDoctrine()->getRepository('RbsSalesBundle:Payment')->updateDeliveredProductValue($delivery);

        $this->dispatch('delivery.updated', new DeliveryEvent($delivery));

        $this->flashMessage('success', 'Delivery Updated Successfully');

        return $this->redirect($this->generateUrl('truck_info_in_out_list'));
    }

    /**
     * @Route("/delivery/vehicle/add", name="delivery_add", options={"expose"=true})
     * @Template("RbsSalesBundle:Delivery:delivery-add.html.twig")
     * @param Request $request
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     * @JMS\Secure(roles="ROLE_DELIVERY_MANAGE")
     */
    public function addDeliveryAction(Request $request)
    {
        $form = $this->createForm(new DeliveryAddForm($this->getUser()));

        if ('POST' === $request->getMethod()) {
            $delivery = new Delivery();
            $delivery->setTransportGiven(Delivery::NOURISH);
            $delivery->setShipped(false);
            $delivery->setDepo($this->getDoctrine()->getRepository('RbsCoreBundle:Depo')->find($request->request->get('delivery_add_form')['depo']));

            $this->getDoctrine()->getRepository('RbsSalesBundle:Delivery')->createDelivery($delivery);
            foreach ($request->request->get('delivery_add_form')['orders'] as $order){
                $orderObj = $this->getDoctrine()->getRepository('RbsSalesBundle:Order')->find($order);
                $delivery->addOrder($orderObj);
                $this->getDoctrine()->getRepository('RbsSalesBundle:Delivery')->update($delivery);
                $this->getDoctrine()->getRepository('RbsSalesBundle:Order')->update($orderObj);
            }
            
            $this->get('session')->getFlashBag()->add(
                'success',
                'Delivery Add Successfully'
            );

            return $this->redirect($this->generateUrl('deliveries_home'));
        }

        return array(
            'form' => $form->createView()
        );
    }

    /**
     * @Route("/order_item_quantity", name="order_item_quantity", options={"expose"=true})
     * @param Request $request
     * @return Response
     */
    public function orderItemQuantityAction(Request $request)
    {
        $orderItemId = $request->request->get('orderItemId');
        $deliveryItems = $this->getDoctrine()->getRepository('RbsSalesBundle:DeliveryItem')->findOneBy(array(
            'orderItem' => $orderItemId
        ));
        $orderItemQuantity = 0;
        foreach ($deliveryItems as $deliveryItem){
            $orderItemQuantity += $deliveryItem->getQty();
        }
        
        $response = new Response(json_encode(array("orderItemQuantity" => $orderItemQuantity)), 200);

        return $response;
    }

    /**
     * @Route("/order/item/remaining/{order}", name="order_item_remaining", options={"expose"=true})
     * @return \Symfony\Component\HttpFoundation\Response
     * @param Request $request
     * @JMS\Secure(roles="ROLE_DELIVERY_MANAGE")
     */
    public function getRemainingOrderItems(Order $order){
        $result = array();
        $deliveredItems = $this->getDoctrine()->getRepository('RbsSalesBundle:DeliveryItem')->getPartialDeliveredItemsByOrder($order);
        /** @var OrderItem $orderItem */
        foreach ($order->getOrderItems() as $orderItem){

            $deliveredItemQty = isset($deliveredItems[$order->getId()][$orderItem->getId()]['delivered'])?$deliveredItems[$order->getId()][$orderItem->getId()]['delivered']:0;
            if($orderItem->getQuantity()>$deliveredItemQty){
                $result[$orderItem->getItem()->getId()]= $orderItem->getItem()->getItemCodeName();
            }
        }
        return new JsonResponse($result);
    }
}