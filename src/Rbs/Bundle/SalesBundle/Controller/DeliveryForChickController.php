<?php

namespace Rbs\Bundle\SalesBundle\Controller;

use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use JMS\SecurityExtraBundle\Annotation as JMS;
use Rbs\Bundle\CoreBundle\Entity\Depo;
use Rbs\Bundle\CoreBundle\Repository\DepoRepository;
use Rbs\Bundle\SalesBundle\Entity\Delivery;
use Rbs\Bundle\SalesBundle\Entity\Order;
use Rbs\Bundle\SalesBundle\Entity\Vehicle;
use Rbs\Bundle\SalesBundle\Entity\Agent;
use Rbs\Bundle\SalesBundle\Entity\VehicleNourish;
use Rbs\Bundle\SalesBundle\Event\DeliveryEvent;
use Rbs\Bundle\SalesBundle\Form\Type\DeliveryAddForm;
use Rbs\Bundle\SalesBundle\Form\Type\DeliveryForm;
use Rbs\Bundle\SalesBundle\Repository\AgentRepository;
use Rbs\Bundle\SalesBundle\Repository\VehicleNourishRepository;
use Rbs\Bundle\UserBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints\IsTrue;

/**
 * DeliveryForChick Controller.
 *
 */
class DeliveryForChickController extends BaseController
{
    /**
     * @Route("/chick/challan/add", name="chick_deliveries_home")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @JMS\Secure(roles="ROLE_CHICK_DELIVERY_MANAGE")
     */
    public function indexAction(Request $request)
    {
        $datatable = $this->get('rbs_erp.sales.datatable.delivery.chick');
        $datatable->buildDatatable();

        return $this->render('RbsSalesBundle:Delivery:index.html.twig', array(
            'datatable' => $datatable,
        ));
    }

    /**
     * @Route("/chick/delivery_list_ajax", name="delivery_chick_list_ajax", options={"expose"=true})
     * @Method("GET")
     * @JMS\Secure(roles="ROLE_CHICK_DELIVERY_MANAGE")
     * @param Request $request
     * @return Response
     */
    public function listAjaxAction(Request $request)
    {
        $datatable = $this->get('rbs_erp.sales.datatable.delivery.chick');
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
     * @Route("/vehicle/in", name="vehicle_in")
     * @Template("RbsSalesBundle:DeliveryChick:vehicle-in.html.twig")
     * @param Request $request
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse|Response
     * @JMS\Secure(roles="ROLE_CHICK_DELIVERY_MANAGE, ROLE_CHICK_TRUCK_IN")
     */
    public function vehicleInForChickAction(Request $request){

        $user = $this->getUser();
        $vehicle = new Vehicle();
        $form = $this->createFormBuilder();
        $form->add('vehicleNourish', 'entity', array(
            'class' => 'RbsSalesBundle:VehicleNourish',
            'placeholder' => 'Select Vehicle',
            'property' => 'truckInformation',
            'required'=>false,
            'query_builder' => function (VehicleNourishRepository $repository)  use ($user)
            {
                return $repository->createQueryBuilder('v')
                    ->join("v.depo", "d")
                    ->join("d.users", "u")
                    ->andWhere('d.deletedAt IS NULL')
                    ->andWhere("u = :user")
                    ->setParameter('user', $user);

            }
        ))
            ->add('chickAgent', 'entity', array(
            'class' => 'RbsSalesBundle:Agent',
            'attr'=>array('class'=>'select2me'),
            'placeholder' => 'Select Agent',
            'property' => 'idNameForChick',
            'required'=>false,
            'query_builder' => function (AgentRepository $repository)
            {
                return $repository->createQueryBuilder('a')
                    ->join('a.user', 'u')
                    ->join('u.profile', 'p')
                    ->join('u.zilla', 'z')
                    ->where('u.userType = :AGENT')
                    ->andWhere('u.enabled = 1')
                    ->andWhere('u.deletedAt IS NULL')
                    ->andWhere('a.chickAgentID IS NOT NULL')
                    ->setParameter('AGENT', User::AGENT)
                    ->orderBy('p.fullName','ASC');

            }
            ))
            ->add('agentDepot', 'entity', array(
            'class' => 'RbsCoreBundle:Depo',
            'placeholder' => 'Select Depo',
            'property' => 'name',
            'required'=>false,
            'query_builder' => function (DepoRepository $repository) use ($user)
            {
                return $repository->createQueryBuilder('d')
                    ->join("d.users", "u")
                    ->where("d.depotType = :depotType")
                    ->andWhere("u = :user")
                    ->setParameter('depotType', Depo::DEPOT_TYPE_CHICK)
                    ->setParameter('user', $user);


            }
            ))
            ->add('agent_own_vehicle','checkbox',array(
                'attr'=>array('class'=>'agent_own_vehicle'),
                'label'=>'If provided vehicle by agent',
                'required'=>false,
                ))
            ->add('driverName', 'text', array(
                'required' => false
            ))
            ->add('driverPhone', 'text', array(
                'required' => false,
                'max_length' => 50
            ))
            ->add('truckNumber', 'text', array(
                'required' => false
            ))

            ->add('submit','submit',array('attr'=>array('class'=>'btn btn-primary downloadpdf-btn'),'label'=>'Assign >>'));
        $form = $form->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            $data = $form->getData();
            // be absolutely sure they agree
            if (true === $form['agent_own_vehicle']->getData()) {
                $vehicle->setDepo($this->em()->getRepository('RbsCoreBundle:Depo')->find($data['agentDepot']));
                $vehicle->setAgent($this->em()->getRepository('RbsSalesBundle:Agent')->find($data['chickAgent']));
                $vehicle->setDriverName($data['driverName']);
                $vehicle->setDriverPhone($data['driverPhone']);
                $vehicle->setTruckNumber($data['truckNumber']);
                $vehicle->setTransportGiven(Vehicle::AGENT);
                $vehicle->setShipped(false);
                $vehicle->setVehicleIn(new \DateTime());
                $vehicle->setTransportStatus(Vehicle::IN);
                $this->getDoctrine()->getRepository('RbsSalesBundle:Vehicle')->create($vehicle);

            }else{
                if(empty($data['vehicleNourish'])){
                    $this->flashMessage('error', 'Vehicle is required.');
                    return $this->redirect($this->generateUrl('vehicle_in'));
                }else{
                    /** @var VehicleNourish $nourishVehicle */
                    $nourishVehicle = $this->em()->getRepository('RbsSalesBundle:VehicleNourish')->find($data['vehicleNourish']);

                    $vehicle->setDepo($nourishVehicle->getDepo());
                    $vehicle->setDriverName($nourishVehicle->getDriverName());
                    $vehicle->setDriverPhone($nourishVehicle->getDriverPhone());
                    $vehicle->setTruckNumber($nourishVehicle->getTruckNumber());
                    $vehicle->setTransportGiven(Vehicle::NOURISH);
                    $vehicle->setShipped(false);
                    $vehicle->setVehicleIn(new \DateTime());
                    $vehicle->setTransportStatus(Vehicle::IN);

                    $this->getDoctrine()->getRepository('RbsSalesBundle:Vehicle')->create($vehicle);
                    $this->get('session')->getFlashBag()->add('success', 'Vehicle In Successfully');

                }

            }
            return $this->redirect($this->generateUrl('chick_delivery_set', array('id' => $vehicle->getId())));
        }

        return array(
            'form' => $form->createView(),
        );

    }


    /**
     * @Route("/chick/delivery/{id}", name="chick_delivery_view", options={"expose"=true})
     * @param Delivery $delivery
     * @return \Symfony\Component\HttpFoundation\Response
     * @JMS\Secure(roles="ROLE_CHICK_DELIVERY_MANAGE, ROLE_SUPER_ADMIN")
     */
    public function view(Delivery $delivery)
    {
        $partialItems = $this->getDoctrine()->getRepository('RbsSalesBundle:DeliveryItem')->getPartialDeliveredItems($delivery);
        return $this->render('RbsSalesBundle:DeliveryChick:view.html.twig', array(
            'delivery'      => $delivery,
            'partialItems'  => $partialItems,
        ));
    }

    /**
     * @Route("/chick/delivery-save/{id}", name="chick_delivery_save")
     * @param Request $request
     * @param Delivery $delivery
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     * @JMS\Secure(roles="ROLE_CHICK_DELIVERY_MANAGE")
     */
    public function deliverySetAction(Request $request, Delivery $delivery)
    {
        $data = $this->getDoctrine()->getRepository('RbsSalesBundle:Delivery')->saveForChick($delivery, $this->get('request')->request->all());
        $this->getDoctrine()->getRepository('RbsSalesBundle:Order')->updateDeliveryState($data['orders'], true);
        $this->getDoctrine()->getRepository('RbsSalesBundle:Stock')->removeStock($delivery);
        $this->getDoctrine()->getRepository('RbsSalesBundle:Payment')->createDeliveredProductValue($delivery);
        /** @var Vehicle $vehicle */
        foreach ($delivery->getVehicles() as $vehicle){
            $this->updateVehicleStatus($vehicle);
        }

        if (!empty($this->get('request')->request->get('checked-vehicles'))) {
            foreach ($this->get('request')->request->get('checked-vehicles') as $vehicleId => $vehicle) {
                $vehicleObj = $this->getDoctrine()->getRepository('RbsSalesBundle:Vehicle')->find($vehicleId);
                $vehicleObj->setShipped(true);
                $this->getDoctrine()->getRepository('RbsSalesBundle:Vehicle')->update($vehicleObj);
            }
        }

        $this->dispatch('delivery.delivered', new DeliveryEvent($delivery));

        $this->flashMessage('success', $delivery->getId().' Delivery Completed Successfully');

        return $this->redirect($this->generateUrl('chick_truck_info_list'));
    }

    /**
     * @Route("/chick/delivery/update/{id}", name="chick_delivery_edit", options={"expose"=true})
     * @param Delivery $delivery
     * @return \Symfony\Component\HttpFoundation\Response
     * @JMS\Secure(roles="ROLE_CHICK_DELIVERY_MANAGE, ROLE_SUPER_ADMIN")
     */
    public function update(Delivery $delivery)
    {
        $partialItems = $this->getDoctrine()->getRepository('RbsSalesBundle:DeliveryItem')->getPartialDeliveredItems($delivery);
        $deliveryItems = $this->getDoctrine()->getRepository('RbsSalesBundle:DeliveryItem')->getDeliveredItemId($delivery);
        return $this->render('RbsSalesBundle:DeliveryChick:edit.html.twig', array(
            'delivery'      => $delivery,
            'partialItems'  => $partialItems,
            'deliveryItem'  => $deliveryItems,
        ));
    }

    /**
     * @Route("/chick/delivery-update/{id}", name="chick_delivery_update")
     * @param Request $request
     * @param Delivery $delivery
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     * @JMS\Secure(roles="ROLE_CHICK_DELIVERY_MANAGE, ROLE_SUPER_ADMIN")
     */
    public function deliveryUpdateAction(Request $request, Delivery $delivery)
    {
        $this->getDoctrine()->getRepository('RbsSalesBundle:Delivery')->updateForChick($delivery, $this->get('request')->request->all());
        $this->getDoctrine()->getRepository('RbsSalesBundle:Payment')->updateDeliveredProductValue($delivery);

        if (!empty($this->get('request')->request->get('checked-vehicles'))) {
            foreach ($this->get('request')->request->get('checked-vehicles') as $vehicleId => $vehicle) {
                $vehicleObj = $this->getDoctrine()->getRepository('RbsSalesBundle:Vehicle')->find($vehicleId);
                $vehicleObj->setShipped(true);
                $this->getDoctrine()->getRepository('RbsSalesBundle:Vehicle')->update($vehicleObj);
            }
        }

        $this->dispatch('delivery.delivered', new DeliveryEvent($delivery));

        $this->flashMessage('success', 'Delivery Completed Successfully');

        return $this->redirect($this->generateUrl('chick_truck_info_list'));
    }

    /**
     * @Route("/chick/order-remove-form-delivery/{delivery}/{order}", name="chick_order_reomve_from_delivery")
     * @param Request $request
     * @param Delivery $delivery
     * @param Order $order
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     * @JMS\Secure(roles="ROLE_CHICK_DELIVERY_MANAGE, ROLE_SUPER_ADMIN")
     */
    public function removeOrderFormDelivery(Request $request, Delivery $delivery, Order $order){

        $order->setVehicleState(null);

        $delivery->removeOrder($order);
        $this->em()->persist($order);
        $this->em()->persist($delivery);
        $this->em()->flush();

        return $this->redirect($this->generateUrl('chick_delivery_view', array('id' => $delivery->getId())));


    }

    private function updateVehicleStatus(Vehicle $vehicle){

        $vehicle->setShipped(true);
        $vehicle->setStartLoad(new \DateTime());
        $vehicle->setFinishLoad(new \DateTime());
        $vehicle->setVehicleOut(new \DateTime());
        $vehicle->setTransportStatus(Vehicle::OUT);
        $this->getDoctrine()->getRepository('RbsSalesBundle:Vehicle')->update($vehicle);

    }


    /**
     * @return \Doctrine\Common\Persistence\ObjectManager|object
     */
    protected function em()
    {
        return $this->getDoctrine()->getManager();
    }


}