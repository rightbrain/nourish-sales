<?php

namespace Rbs\Bundle\SalesBundle\Controller;

use Doctrine\ORM\QueryBuilder;
use Rbs\Bundle\SalesBundle\Entity\Agent;
use Rbs\Bundle\SalesBundle\Entity\Delivery;
use Rbs\Bundle\SalesBundle\Entity\Vehicle;
use Rbs\Bundle\SalesBundle\Event\DeliveryEvent;
use Rbs\Bundle\SalesBundle\Form\Type\VehicleDeliveryForm;
use Rbs\Bundle\SalesBundle\Form\Type\VehicleDeliverySetForm;
use Rbs\Bundle\SalesBundle\Form\Type\VehicleForm;
use Rbs\Bundle\UserBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use JMS\SecurityExtraBundle\Annotation as JMS;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Vehicle Controller.
 *
 */
class VehicleController extends BaseController
{
    /**
     * @Route("/vehicle/list", name="truck_info_list")
     * @Method("GET")
     * @Template()
     * @JMS\Secure(roles="ROLE_TRUCK_MANAGE")
     */
    public function indexAction()
    {
        $datatable = $this->get('rbs_erp.sales.datatable.vehicle');
        $datatable->buildDatatable();

        return $this->render('RbsSalesBundle:Vehicle:index.html.twig', array(
            'datatable' => $datatable
        ));
    }

    /**
     * @Route("/truck_info_list_ajax", name="truck_info_list_ajax", options={"expose"=true})
     * @Method("GET")
     * @JMS\Secure(roles="ROLE_TRUCK_MANAGE")
     */
    public function listAjaxAction()
    {
        $datatable = $this->get('rbs_erp.sales.datatable.vehicle');
        $datatable->buildDatatable();

        $query = $this->get('sg_datatables.query')->getQueryFrom($datatable);
        /** @var QueryBuilder $qb */
        $function = function($qb)
        {

        };
        $query->addWhereAll($function);
        
        return $query->getResponse();
    }

    /**
     * @Route("/vehicle/my/list", name="truck_info_my_list", options={"expose"=true})
     * @Method("GET")
     * @Template()
     * @JMS\Secure(roles="ROLE_AGENT")
     */
    public function myIndexAction()
    {
        $datatable = $this->get('rbs_erp.sales.datatable.my.vehicle');
        $datatable->buildDatatable();

        return $this->render('RbsSalesBundle:Vehicle:index.html.twig', array(
            'datatable' => $datatable
        ));
    }

    /**
     * @Route("/truck_info_my_list_ajax", name="truck_info_my_list_ajax", options={"expose"=true})
     * @Method("GET")
     * @JMS\Secure(roles="ROLE_AGENT")
     */
    public function myListAjaxAction()
    {
        $datatable = $this->get('rbs_erp.sales.datatable.my.vehicle');
        $datatable->buildDatatable();
        $agent = $this->getDoctrine()->getRepository('RbsSalesBundle:Agent')->findOneBy(array(
            'user' => $this->getUser()
        ));
        $query = $this->get('sg_datatables.query')->getQueryFrom($datatable);
        /** @var QueryBuilder $qb */
        $function = function($qb) use ($agent)
        {
            $qb->join('sales_vehicles.agent', 'a');
            $qb->andWhere('a =:agent');
            $qb->andWhere('sales_vehicles.transportGiven =:AGENT');
            $qb->setParameters(array('agent'=>$agent,'AGENT'=>Vehicle::AGENT));
        };
        $query->addWhereAll($function);
        
        return $query->getResponse();
    }

    /**
     * @Route("/vehicle/in/out/list", name="truck_info_in_out_list", options={"expose"=true})
     * @Method("GET")
     * @Template()
     * @JMS\Secure(roles="ROLE_DELIVERY_MANAGE, ROLE_TRUCK_IN, ROLE_TRUCK_START, ROLE_TRUCK_FINISH, ROLE_TRUCK_OUT")
     */
    public function inOutIndexAction()
    {
        $datatable = $this->get('rbs_erp.sales.datatable.in.out.vehicle');
        $datatable->buildDatatable();

        return $this->render('RbsSalesBundle:Delivery:truck-in-out.html.twig', array(
            'datatable' => $datatable
        ));
    }
    
    /**
     * @Route("/truck_info_in_out_list_ajax", name="truck_info_in_out_list_ajax", options={"expose"=true})
     * @Method("GET")
     * @JMS\Secure(roles="ROLE_DELIVERY_MANAGE, ROLE_TRUCK_IN, ROLE_TRUCK_START, ROLE_TRUCK_FINISH, ROLE_TRUCK_OUT")
     */
    public function inOutListAjaxAction()
    {
        $em = $this->getDoctrine()->getManager();
        $datatable = $this->get('rbs_erp.sales.datatable.in.out.vehicle');
        $datatable->buildDatatable();

        $getDepoId = $em->getRepository('RbsCoreBundle:Depo')->getDepoId($this->getUser()->getId());
        $getDepoId ? $depoId = $getDepoId[0]['id'] : $depoId = 0;
        
        $query = $this->get('sg_datatables.query')->getQueryFrom($datatable);
        /** @var QueryBuilder $qb */
        $function = function($qb) use ($depoId)
        {
            if($depoId == 0){
                $qb->join('sales_vehicles.depo', 'd');
                $qb->andWhere('sales_vehicles.vehicleOut IS NULL');
            }else{
                $qb->join('sales_vehicles.depo', 'd');
                $qb->andWhere('d.id =:depoId');
                $qb->andWhere('sales_vehicles.vehicleOut IS NULL');
                $qb->setParameters(array('depoId'=>$depoId));
            }
        };
        $query->addWhereAll($function);

        return $query->getResponse();
    }
    
    /**
     * @Route("/vehicle/add", name="truck_info_add")
     * @Template("RbsSalesBundle:Vehicle:form.html.twig")
     * @param Request $request
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse|Response
     * @JMS\Secure(roles="ROLE_AGENT, ROLE_TRUCK_MANAGE")
     */
    public function addAction(Request $request)
    {
        $vehicle = new Vehicle();

        $form = $this->createForm(new VehicleForm($this->getUser()), $vehicle);

        if ('POST' === $request->getMethod()) {
            $form->handleRequest($request);

            if ($form->isValid()) {

                if($this->getUser()->getUserType() == User::AGENT){
                    $agent = $this->getDoctrine()->getRepository('RbsSalesBundle:Agent')->findOneBy(array(
                        'user' => $this->getUser()
                    ));
                    $vehicle->setAgent($agent);
                    $vehicle->setTransportGiven(Vehicle::AGENT);
                    $vehicle->setDepo($agent->getDepo());
                    $vehicle->setOrderText($request->request->get('vehicle')['orders']);
                    $delivery = $this->getDoctrine()->getRepository('RbsSalesBundle:Delivery')->findOneByOrderId($request->request->get('vehicle')['orders']);
                    if(!$delivery){
                        $order = $this->getDoctrine()->getRepository('RbsSalesBundle:Order')->find($request->request->get('vehicle')['orders']);
                        $delivery = new Delivery();
                        $delivery->addOrder($order);
                        $delivery->setDepo($agent->getDepo());
                        $delivery->setTransportGiven(Delivery::AGENT);
                        $this->getDoctrine()->getRepository('RbsSalesBundle:Delivery')->createDelivery($delivery);
                    }
                    $vehicle->setDeliveries($delivery);
                }else{
                    $vehicle->setTransportGiven(Vehicle::NOURISH);
                }

                $this->getDoctrine()->getRepository('RbsSalesBundle:Vehicle')->create($vehicle);

                $this->get('session')->getFlashBag()->add(
                    'success',
                    'Agent Vehicle Info Add Successfully!'
                );
                if($this->getUser()->getUserType() == User::AGENT){
                    return $this->redirect($this->generateUrl('truck_info_my_list'));
                }else{
                    return $this->redirect($this->generateUrl('truck_info_list'));
                }
            }
        }

        return array(
            'form' => $form->createView(),
            'user' => $this->getUser()
        );
    }

    /**
     * @Route("/vehicle/in/{id}", name="truck_in", options={"expose"=true})
     * @param Vehicle $vehicle
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @JMS\Secure(roles="ROLE_DELIVERY_MANAGE, ROLE_TRUCK_IN")
     */
    public function truckInAction(Vehicle $vehicle)
    {
        $vehicle->setVehicleIn(new \DateTime());
        $vehicle->setTransportStatus(Vehicle::IN);
        $this->getDoctrine()->getRepository('RbsSalesBundle:Vehicle')->update($vehicle);

        $this->get('session')->getFlashBag()->add(
            'success',
            'Vehicle In Successfully'
        );

        return $this->redirect($this->generateUrl('truck_info_in_out_list'));
    }

    /**
     * @Route("/vehicle/start/{id}", name="truck_start", options={"expose"=true})
     * @param Vehicle $vehicle
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @JMS\Secure(roles="ROLE_DELIVERY_MANAGE, ROLE_TRUCK_START")
     */
    public function truckStartAction(Vehicle $vehicle)
    {
        $vehicle->setStartLoad(new \DateTime());
        $vehicle->setTransportStatus(Vehicle::START_LOAD);
        $this->getDoctrine()->getRepository('RbsSalesBundle:Vehicle')->update($vehicle);

        $this->get('session')->getFlashBag()->add(
            'success',
            'Vehicle Load Successfully Start'
        );

        return $this->redirect($this->generateUrl('truck_info_in_out_list'));
    }

    /**
     * @Route("/delivery/set/{id}", name="delivery_set", options={"expose"=true})
     * @param Vehicle $vehicle
     * @param Request $request
     * @Template("RbsSalesBundle:Vehicle:vehicle-delivery-set-form.html.twig")
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse|Response
     * @JMS\Secure(roles="ROLE_DELIVERY_MANAGE")
     */
    
    public function deliverySetAction(Request $request, Vehicle $vehicle)
    {
        $form = $this->createForm(new VehicleDeliverySetForm($this->getUser(), $vehicle->getId()));

        if ('POST' === $request->getMethod()) {
            $delivery = new Delivery();
            $delivery->setDepo($vehicle->getDepo());
            $delivery->setTransportGiven(Delivery::NOURISH);
            $this->getDoctrine()->getRepository('RbsSalesBundle:Delivery')->createDelivery($delivery);
            foreach ($request->request->get('vehicle_delivery_form')['orders'] as $order){
                $delivery->addOrder($this->getDoctrine()->getRepository('RbsSalesBundle:Order')->find($order));
                $this->getDoctrine()->getRepository('RbsSalesBundle:Delivery')->update($delivery);
            }
            $vehicle->setDeliveries($delivery);
            $this->getDoctrine()->getRepository('RbsSalesBundle:Vehicle')->update($vehicle);

            $this->get('session')->getFlashBag()->add(
                'success',
                'Vehicle Delivery Set Successfully Start'
            );

            return $this->redirect($this->generateUrl('deliveries_home'));
        }

        return array(
            'form' => $form->createView()
        );
    }

    /**
     * @Route("/vehicle/finish/{id}", name="truck_finish", options={"expose"=true})
     * @param Vehicle $vehicle
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @JMS\Secure(roles="ROLE_DELIVERY_MANAGE, ROLE_TRUCK_FINISH")
     */
    public function truckFinishAction(Vehicle $vehicle)
    {
        $vehicle->setFinishLoad(new \DateTime());
        $vehicle->setTransportStatus(Vehicle::FINISH_LOAD);
        $this->getDoctrine()->getRepository('RbsSalesBundle:Vehicle')->update($vehicle);

        $this->get('session')->getFlashBag()->add(
            'success',
            'Vehicle Successfully Finished'
        );

        return $this->redirect($this->generateUrl('truck_info_in_out_list'));
    }

    /**
     * @Route("/vehicle/out/{id}", name="truck_out", options={"expose"=true})
     * @param Vehicle $vehicle
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @JMS\Secure(roles="ROLE_DELIVERY_MANAGE, ROLE_TRUCK_OUT")
     */
    public function truckOutAction(Vehicle $vehicle)
    {
        $vehicle->setVehicleOut(new \DateTime());
        $vehicle->setTransportStatus(Vehicle::OUT);
        $this->getDoctrine()->getRepository('RbsSalesBundle:Vehicle')->update($vehicle);

        $this->get('session')->getFlashBag()->add(
            'success',
            'Vehicle Successfully Out'
        );

        return $this->redirect($this->generateUrl('truck_info_in_out_list'));
    }

    /**
     * @Route("/vehicle/with/delivery/list", name="truck_with_delivery_list", options={"expose"=true})
     * @Method("GET")
     * @Template()
     * @JMS\Secure(roles="ROLE_DELIVERY_MANAGE, ROLE_TRUCK_IN, ROLE_TRUCK_START, ROLE_TRUCK_FINISH, ROLE_TRUCK_OUT")
     */
    public function truckWithDeliveryListAction()
    {
        $datatable = $this->get('rbs_erp.sales.datatable.vehicle.with.delivery');
        $datatable->buildDatatable();

        return $this->render('RbsSalesBundle:Vehicle:truck-with-delivery-index.html.twig', array(
            'datatable' => $datatable
        ));
    }

    /**
     * @Route("/truck_with_delivery_list_ajax", name="truck_with_delivery_list_ajax", options={"expose"=true})
     * @Method("GET")
     * @JMS\Secure(roles="ROLE_DELIVERY_MANAGE, ROLE_TRUCK_IN, ROLE_TRUCK_START, ROLE_TRUCK_FINISH, ROLE_TRUCK_OUT")
     */
    public function truckWithDeliveryListAjaxAction()
    {
        $em = $this->getDoctrine()->getManager();
        $datatable = $this->get('rbs_erp.sales.datatable.vehicle.with.delivery');
        $datatable->buildDatatable();

        $getDepoId = $em->getRepository('RbsCoreBundle:Depo')->getDepoId($this->getUser()->getId());
        $getDepoId ? $depoId = $getDepoId[0]['id'] : $depoId = 0;

        $query = $this->get('sg_datatables.query')->getQueryFrom($datatable);
        /** @var QueryBuilder $qb */
        $function = function($qb) use ($depoId)
        {
            if($depoId == 0){
                $qb->andWhere('sales_vehicles.vehicleOut IS NULL');
                $qb->andWhere('sales_vehicles.vehicleIn IS NOT NULL');
                $qb->andWhere('sales_vehicles.transportGiven = :NOURISH');
                $qb->setParameter('NOURISH', Vehicle::NOURISH);
            }else{
                $qb->join('sales_vehicles.depo', 'd');
                $qb->andWhere('d.id =:depoId');
                $qb->andWhere('sales_vehicles.vehicleOut IS NULL');
                $qb->andWhere('sales_vehicles.vehicleIn IS NOT NULL');
                $qb->andWhere('sales_vehicles.transportGiven = :NOURISH');
                $qb->setParameters(array('depoId'=>$depoId, 'NOURISH'=>Vehicle::NOURISH));
            }
        };
        $query->addWhereAll($function);

        return $query->getResponse();
    }

    /**
     * @Route("/vehicle/add/with/delivery/{id}", name="set_truck_with_delivery", options={"expose"=true})
     * @Template("RbsSalesBundle:Vehicle:truck-with-delivery.html.twig")
     * @param Request $request
     * @param Vehicle $vehicle
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse|Response
     * @JMS\Secure(roles="ROLE_DELIVERY_MANAGE, ROLE_TRUCK_IN, ROLE_TRUCK_START, ROLE_TRUCK_FINISH, ROLE_TRUCK_OUT")
     */
    public function setTruckWithDeliveryAction(Request $request, Vehicle $vehicle)
    {
        $form = $this->createForm(new VehicleDeliveryForm($this->getUser(), $vehicle), $vehicle);

        if ('POST' === $request->getMethod()) {
            $form->handleRequest($request);

            if ($form->isValid()) {

                $delivery = $this->getDoctrine()->getRepository('RbsSalesBundle:Delivery')->find($request->request->get('vehicle')['deliveries']);

                $vehicle->setTruckInvoiceAttachedBy($this->getUser());
                $vehicle->setTruckInvoiceAttachedAt(new \DateTime());

                $this->getDoctrine()->getRepository('RbsSalesBundle:Vehicle')->update($vehicle);

                $this->get('session')->getFlashBag()->add(
                    'success',
                    'Set Vehicle With Delivery Successfully!'
                );
              
                return $this->redirect($this->generateUrl('truck_with_delivery_list'));
            }
        }

        return array(
            'form' => $form->createView()
        );
    }
}