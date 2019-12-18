<?php

namespace Rbs\Bundle\SalesBundle\Controller;

use Doctrine\ORM\QueryBuilder;
use Rbs\Bundle\CoreBundle\Entity\Depo;
use Rbs\Bundle\SalesBundle\Entity\Agent;
use Rbs\Bundle\SalesBundle\Entity\Delivery;
use Rbs\Bundle\SalesBundle\Entity\Order;
use Rbs\Bundle\SalesBundle\Entity\Vehicle;
use Rbs\Bundle\SalesBundle\Form\Type\VehicleDeliverySetChickForm;
use Rbs\Bundle\SalesBundle\Form\Type\VehicleDeliverySetForm;
use Rbs\Bundle\SalesBundle\Form\Type\VehicleForm;
use Rbs\Bundle\UserBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use JMS\SecurityExtraBundle\Annotation as JMS;

/**
 * Vehicle Controller.
 *
 */
class VehicleForChickController extends BaseController
{
    /**
     * @Route("/chick/vehicle/list", name="chick_truck_info_list")
     * @Method("GET")
     * @JMS\Secure(roles="ROLE_CHICK_TRUCK_MANAGE, ROLE_SUPER_ADMIN")
     */
    public function indexAction()
    {
        $datatable = $this->get('rbs_erp.sales.datatable.chick.vehicle');
        $datatable->buildDatatable();

        return $this->render('RbsSalesBundle:Vehicle:index.html.twig', array(
            'datatable' => $datatable
        ));
    }

    /**
     * @Route("/chick/truck_info_list_ajax", name="chick_truck_info_list_ajax", options={"expose"=true})
     * @Method("GET")
     * @JMS\Secure(roles="ROLE_CHICK_TRUCK_MANAGE, ROLE_SUPER_ADMIN")
     */
    public function listAjaxAction()
    {
        $user = $this->getUser();
        $datatable = $this->get('rbs_erp.sales.datatable.chick.vehicle');
        $datatable->buildDatatable();

        $query = $this->get('sg_datatables.query')->getQueryFrom($datatable);
        /** @var QueryBuilder $qb */
        $function = function($qb) use ($user)
        {
            $qb->join("sales_vehicles.depo", "d");
            $qb->join("d.users", "u");
            $qb->where('d.depotType = :type');
            $qb->setParameter('type',Depo::DEPOT_TYPE_CHICK);
            if(!in_array('ROLE_SUPER_ADMIN', $user->getRoles())){
                $qb->andWhere("u = :user");
                $qb->setParameter('user', $user);
            }
        };
        $query->addWhereAll($function);

        return $query->getResponse();
    }

    /**
     * @Route("/chick/vehicle/in/out/list", name="chick_truck_info_in_out_list", options={"expose"=true})
     * @Method("GET")
     * @JMS\Secure(roles="ROLE_CHICK_TRUCK_MANAGE")
     */
    public function inOutIndexAction()
    {
        $datatable = $this->get('rbs_erp.sales.datatable.in.out.chick.vehicle');
        $datatable->buildDatatable();

        return $this->render('RbsSalesBundle:Delivery:vehicle.html.twig', array(
            'datatable' => $datatable
        ));
    }
    
    /**
     * @Route("/chick/truck_info_in_out_list_ajax", name="chick_truck_info_in_out_list_ajax", options={"expose"=true})
     * @Method("GET")
     * @JMS\Secure(roles="ROLE_CHICK_TRUCK_MANAGE")
     */
    public function inOutListAjaxAction()
    {
        $datatable = $this->get('rbs_erp.sales.datatable.in.out.vehicle');
        $datatable->buildDatatable();
        $depoId = $this->checkUserDepo();

        $query = $this->get('sg_datatables.query')->getQueryFrom($datatable);
        /** @var QueryBuilder $qb */
        $function = function($qb) use ($depoId)
        {
            $qb->join('sales_vehicles.depo', 'd');
            $qb->andWhere('sales_vehicles.vehicleOut IS NULL');

            $qb->orderBy('sales_vehicles.createdAt' ,'DESC');

            if($depoId != 0){
                $qb->andWhere('d.id =:depoId');
                $qb->setParameters(array('depoId'=>$depoId));
            }
        };
        $query->addWhereAll($function);

        return $query->getResponse();
    }

    /**
     * @Route("/chick/delivery/set/{id}", name="chick_delivery_set", options={"expose"=true})
     * @param Vehicle $vehicle
     * @param Request $request
     * @Template("RbsSalesBundle:Vehicle:vehicle-delivery-set-chick-form.html.twig")
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse|Response
     * @JMS\Secure(roles="ROLE_CHICK_DELIVERY_MANAGE")
     */
    public function chickDeliverySetAction(Request $request, Vehicle $vehicle)
    {
        $form = $this->createForm(new VehicleDeliverySetChickForm($this->getUser(), $vehicle));

        if ('POST' === $request->getMethod()) {
            if(!isset($request->request->get('vehicle_delivery_form')['orders'])){
                $this->get('session')->getFlashBag()->add('error', 'Please select at least Order');
                return $this->redirect($this->generateUrl('chick_delivery_set', array('id' => $request->attributes->all()['id'])));
            }
            $delivery = new Delivery();
            $orderText=null;
            foreach ($request->request->get('vehicle_delivery_form')['orders'] as $orderId){
                $order = $this->em()->getRepository('RbsSalesBundle:Order')->find($orderId);
                $delivery->addOrder($order);
                $delivery->setDepo($order->getDepo());
                $orderText .= $orderId .', ';

                $order->setVehicleState(Order::VEHICLE_STATE_IN);
                $this->em()->getRepository('RbsSalesBundle:Order')->update($order);
            }
            $delivery->setShipped(false);
            $delivery->setTransportGiven(Delivery::NOURISH);
            $this->em()->getRepository('RbsSalesBundle:Delivery')->createDelivery($delivery);

            $vehicle->setTruckInvoiceAttachedBy($this->getUser());
            $vehicle->setTruckInvoiceAttachedAt(new \DateTime());
            $vehicle->setDeliveries($delivery);
            $vehicle->setShipped(false);
            $vehicle->setOrderText($orderText);

            $this->vehicleRepo()->update($vehicle);
            $this->get('session')->getFlashBag()->add('success', 'Vehicle Delivery Set Started Successfully');

            return $this->redirect($this->generateUrl('chick_deliveries_home'));
        }

        return array(
            'form' => $form->createView()
        );
    }

    /**
     * @Route("/chick/vehicle/finish/{id}", name="chick_truck_finish", options={"expose"=true})
     * @param Vehicle $vehicle
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @JMS\Secure(roles="ROLE_CHICK_DELIVERY_MANAGE, ROLE_CHICK_TRUCK_FINISH")
     */
    public function loadFinishAction(Vehicle $vehicle)
    {
        $vehicle->setFinishLoad(new \DateTime());
        $vehicle->setTransportStatus(Vehicle::FINISH_LOAD);
        $this->vehicleRepo()->update($vehicle);
        $this->get('session')->getFlashBag()->add('success', 'Vehicle Load Finished Successfully');

        return $this->redirect($this->generateUrl('chick_truck_info_in_out_list'));
    }

    /**
     * @Route("/chick/vehicle/out/{id}", name="chick_truck_out", options={"expose"=true})
     * @param Vehicle $vehicle
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @JMS\Secure(roles="ROLE_CHICK_DELIVERY_MANAGE, ROLE_CHICK_TRUCK_OUT")
     */
    public function truckOutAction(Vehicle $vehicle)
    {
        $vehicle->setVehicleOut(new \DateTime());
        $vehicle->setTransportStatus(Vehicle::OUT);
        $vehicle->setShipped(true);
        $this->vehicleRepo()->update($vehicle);
        $this->get('session')->getFlashBag()->add('success', 'Vehicle Out Successfully');

        return $this->redirect($this->generateUrl('chick_truck_info_in_out_list'));
    }

    /**
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    protected function checkUserTypeForRedirect()
    {
        return $this->getUser()->getUserType() == User::AGENT ?
            $this->redirect($this->generateUrl('truck_info_my_list')) :
            $this->redirect($this->generateUrl('truck_info_list'));
    }

    /**
     * @param Request $request
     * @param Vehicle $vehicle
     */
    protected function checkUserForFieldSet(Request $request, Vehicle $vehicle)
    {
        if ($this->getUser()->getUserType() == User::AGENT) {
            $order = $this->em()->getRepository('RbsSalesBundle:Order')->find($request->request->get('vehicle')['orders']);
            $vehicle->setAgent($order->getAgent());
            $vehicle->setTransportGiven(Vehicle::AGENT);
            $vehicle->setDepo($order->getDepo());
            $vehicle->setOrderText($request->request->get('vehicle')['orders']);
            $delivery = new Delivery();
            $delivery->addOrder($order);
            $delivery->setShipped(false);
            $delivery->setDepo($order->getDepo());
            $delivery->setTransportGiven(Delivery::AGENT);
            $vehicle->setDeliveries($delivery);
            $this->em()->getRepository('RbsSalesBundle:Delivery')->createDelivery($delivery);
        } else {
            $vehicle->setTransportGiven(Vehicle::NOURISH);
        }
        $vehicle->setShipped(false);
    }

    /**
     * @return int
     */
    protected function checkUserDepo()
    {
        $getDepoId = $this->em()->getRepository('RbsCoreBundle:Depo')
                                ->getDepoId($this->getUser()->getId());
        return $getDepoId ? $depoId = $getDepoId[0]['id'] : $depoId = 0;
    }

    /**
     * @return Agent
     */
    protected function getAgent()
    {
        return $this->em()->getRepository('RbsSalesBundle:Agent')->findOneBy(array(
            'user' => $this->getUser()
        ));
    }

    /**
     * @return \Doctrine\Common\Persistence\ObjectManager|object
     */
    protected function em()
    {
        return $this->getDoctrine()->getManager();
    }

    /**
     * @return \Rbs\Bundle\SalesBundle\Repository\VehicleRepository
     */
    protected function vehicleRepo()
    {
        return $this->em()->getRepository('RbsSalesBundle:Vehicle');
    }


    /**
     * @Route("/chick/invoice/{id}", name="chick_vehicle_view", options={"expose"=true})
     * @param Vehicle $vehicle
     * @return \Symfony\Component\HttpFoundation\Response
     * @JMS\Secure(roles="ROLE_USER")
     */
    public function view(Vehicle $vehicle)
    {
        $partialItems = $this->getDoctrine()->getRepository('RbsSalesBundle:DeliveryItem')->getPartialDeliveredItems($vehicle->getDeliveries());
        return $this->render('RbsSalesBundle:Vehicle:view-chick.html.twig', array(
            'vehicle'      => $vehicle,
            'partialItems'  => $partialItems,
        ));
    }

}