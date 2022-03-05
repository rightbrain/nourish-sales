<?php

namespace Rbs\Bundle\SalesBundle\Controller;

use Doctrine\ORM\QueryBuilder;
use Rbs\Bundle\SalesBundle\Entity\Agent;
use Rbs\Bundle\SalesBundle\Entity\Delivery;
use Rbs\Bundle\SalesBundle\Entity\Vehicle;
use Rbs\Bundle\SalesBundle\Entity\VehicleNourish;
use Rbs\Bundle\SalesBundle\Form\Type\VehicleDeliverySetForm;
use Rbs\Bundle\SalesBundle\Form\Type\VehicleForm;
use Rbs\Bundle\SalesBundle\Form\Type\VehicleNourishEditForm;
use Rbs\Bundle\SalesBundle\Form\Type\VehicleNourishForm;
use Rbs\Bundle\UserBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use JMS\SecurityExtraBundle\Annotation as JMS;

/**
 * Vehicle Nourish Controller.
 *
 */
class VehicleNourishController extends BaseController
{
    /**
     * @Route("/nourish/vehicle/list", name="nourish_vehicle_info_list")
     * @Method("GET")
     * @JMS\Secure(roles="ROLE_CHICK_TRUCK_MANAGE")
     */
    public function indexAction()
    {
        $datatable = $this->get('rbs_erp.sales.datatable.VehicleNourish');
        $datatable->buildDatatable();

        return $this->render('RbsSalesBundle:VehicleNourish:index.html.twig', array(
            'datatable' => $datatable
        ));
    }

    /**
     * @Route("/nourish_truck_info_list_ajax", name="nourish_truck_info_list_ajax", options={"expose"=true})
     * @Method("GET")
     * @JMS\Secure(roles="ROLE_CHICK_TRUCK_MANAGE")
     */
    public function listAjaxAction()
    {
        $user = $this->getUser();
        $datatable = $this->get('rbs_erp.sales.datatable.VehicleNourish');
        $datatable->buildDatatable();

        $query = $this->get('sg_datatables.query')->getQueryFrom($datatable);
        /** @var QueryBuilder $qb */
        $function = function($qb)
        {
            $qb->andWhere("sales_vehicles_nourish.deletedAt IS NULL");
        };
        $query->addWhereAll($function);

        return $query->getResponse();
    }

    /**
     * @Route("/nourish/vehicle/add", name="nourish_truck_info_add")
     * @Template("RbsSalesBundle:VehicleNourish:form.html.twig")
     * @param Request $request
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse|Response
     * @JMS\Secure(roles="ROLE_CHICK_TRUCK_MANAGE")
     */
    public function addAction(Request $request)
    {
        $vehicle = new VehicleNourish();
        $form = $this->createForm(new VehicleNourishForm(), $vehicle);

        if ('POST' === $request->getMethod()) {
            $form->handleRequest($request);


            if ($form->isValid()) {

                $getFormData = $request->request->get('vehicle_nourish');
                $depots = $getFormData['depo'];

                foreach ($depots as $depot){
                    $vehicle = new VehicleNourish();

                    $vehicle->setDepo($this->getDoctrine()->getRepository('RbsCoreBundle:Depo')->find($depot));
                    $vehicle->setDriverName($getFormData['driverName']);
                    $vehicle->setDriverPhone($getFormData['driverPhone']);
                    $vehicle->setTruckNumber($getFormData['truckNumber']);
                    $this->vehicleRepo()->create($vehicle);
                }


                $this->get('session')->getFlashBag()->add('success', 'Vehicle Info Added Successfully');
                return $this->redirect($this->generateUrl('nourish_vehicle_info_list'));
            }
        }

        return array(
            'form' => $form->createView(),
            'user' => $this->getUser()
        );
    }

    /**
     * @Route("/nourish/vehicle/edit/{id}", name="nourish_truck_info_edit", options={"expose"=true})
     * @Template("RbsSalesBundle:VehicleNourish:form.html.twig")
     * @param Request $request
     * @param VehicleNourish $vehicle
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse|Response
     * @JMS\Secure(roles="ROLE_CHICK_TRUCK_MANAGE")
     */
    public function editAction(Request $request, VehicleNourish $vehicle)
    {
        $form = $this->createForm(new VehicleNourishEditForm(), $vehicle);

        if ('POST' === $request->getMethod()) {
            $form->handleRequest($request);


            if ($form->isValid()) {
                $this->vehicleRepo()->update($vehicle);

                $this->get('session')->getFlashBag()->add('success', 'Vehicle Info Updated Successfully');
                return $this->redirect($this->generateUrl('nourish_vehicle_info_list'));
            }
        }

        return array(
            'form' => $form->createView(),
            'user' => $this->getUser()
        );
    }

    /**
     * @return \Doctrine\Common\Persistence\ObjectManager|object
     */
    protected function em()
    {
        return $this->getDoctrine()->getManager();
    }

    /**
     * @return \Rbs\Bundle\SalesBundle\Repository\VehicleNourishRepository
     */
    protected function vehicleRepo()
    {
        return $this->em()->getRepository('RbsSalesBundle:VehicleNourish');
    }

}