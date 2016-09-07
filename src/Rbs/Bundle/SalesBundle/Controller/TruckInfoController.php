<?php

namespace Rbs\Bundle\SalesBundle\Controller;

use Doctrine\ORM\QueryBuilder;
use Rbs\Bundle\SalesBundle\Entity\Agent;
use Rbs\Bundle\SalesBundle\Entity\TruckInfo;
use Rbs\Bundle\SalesBundle\Form\Type\TruckDeliveryForm;
use Rbs\Bundle\SalesBundle\Form\Type\TruckInfoForm;
use Rbs\Bundle\UserBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use JMS\SecurityExtraBundle\Annotation as JMS;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * TruckInfo Controller.
 *
 */
class TruckInfoController extends BaseController
{
    /**
     * @Route("/truck/info/list", name="truck_info_list")
     * @Method("GET")
     * @Template()
     * @JMS\Secure(roles="ROLE_TRUCK_MANAGE")
     */
    public function indexAction()
    {
        $datatable = $this->get('rbs_erp.sales.datatable.truck.info');
        $datatable->buildDatatable();

        return $this->render('RbsSalesBundle:Truck:index.html.twig', array(
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
        $datatable = $this->get('rbs_erp.sales.datatable.truck.info');
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
     * @Route("/truck/info/my/list", name="truck_info_my_list", options={"expose"=true})
     * @Method("GET")
     * @Template()
     * @JMS\Secure(roles="ROLE_AGENT")
     */
    public function myIndexAction()
    {
        $datatable = $this->get('rbs_erp.sales.datatable.my.truck.info');
        $datatable->buildDatatable();

        return $this->render('RbsSalesBundle:Truck:index.html.twig', array(
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
        $datatable = $this->get('rbs_erp.sales.datatable.my.truck.info');
        $datatable->buildDatatable();
        $agent = $this->getDoctrine()->getRepository('RbsSalesBundle:Agent')->findOneBy(array(
            'user' => $this->getUser()
        ));
        $query = $this->get('sg_datatables.query')->getQueryFrom($datatable);
        /** @var QueryBuilder $qb */
        $function = function($qb) use ($agent)
        {
            $qb->join('sales_truck_info.agent', 'a');
            $qb->andWhere('a =:agent');
            $qb->andWhere('sales_truck_info.transportGiven =:AGENT');
            $qb->setParameters(array('agent'=>$agent,'AGENT'=>TruckInfo::AGENT));
        };
        $query->addWhereAll($function);
        
        return $query->getResponse();
    }

    /**
     * @Route("/truck/info/in/out/list", name="truck_info_in_out_list", options={"expose"=true})
     * @Method("GET")
     * @Template()
     * @JMS\Secure(roles="ROLE_DELIVERY_MANAGE, ROLE_TRUCK_IN, ROLE_TRUCK_START, ROLE_TRUCK_FINISH, ROLE_TRUCK_OUT")
     */
    public function inOutIndexAction()
    {
        $datatable = $this->get('rbs_erp.sales.datatable.in.out.truck.info');
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
        $datatable = $this->get('rbs_erp.sales.datatable.in.out.truck.info');
        $datatable->buildDatatable();

        $getDepoId = $em->getRepository('RbsCoreBundle:Depo')->getDepoId($this->getUser()->getId());
        $getDepoId ? $depoId = $getDepoId[0]['id'] : $depoId = 0;
        
        $query = $this->get('sg_datatables.query')->getQueryFrom($datatable);
        /** @var QueryBuilder $qb */
        $function = function($qb) use ($depoId)
        {
            if($depoId == 0){
                $qb->join('sales_truck_info.depo', 'd');
                $qb->andWhere('sales_truck_info.vehicleOut IS NULL');
            }else{
                $qb->join('sales_truck_info.depo', 'd');
                $qb->andWhere('d.id =:depoId');
                $qb->andWhere('sales_truck_info.vehicleOut IS NULL');
                $qb->setParameters(array('depoId'=>$depoId));
            }
        };
        $query->addWhereAll($function);

        return $query->getResponse();
    }
    
    /**
     * @Route("/truck/info/add", name="truck_info_add")
     * @Template("RbsSalesBundle:Truck:form.html.twig")
     * @param Request $request
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse|Response
     * @JMS\Secure(roles="ROLE_AGENT, ROLE_TRUCK_MANAGE")
     */
    public function addAction(Request $request)
    {
        $truckInfo = new TruckInfo();

        $form = $this->createForm(new TruckInfoForm($this->getUser()), $truckInfo);

        if ('POST' === $request->getMethod()) {
            $form->handleRequest($request);

            if ($form->isValid()) {

                if($this->getUser()->getUserType() == User::AGENT){
                    $agent = $this->getDoctrine()->getRepository('RbsSalesBundle:Agent')->findOneBy(array(
                        'user' => $this->getUser()
                    ));
                    $truckInfo->setAgent($agent);
                    $truckInfo->setTransportGiven(TruckInfo::AGENT);
                    $truckInfo->setDepo($agent->getDepo());
                }else{
                    $truckInfo->setTransportGiven(TruckInfo::NOURISH);
                }

                $this->getDoctrine()->getRepository('RbsSalesBundle:TruckInfo')->create($truckInfo);

                $this->get('session')->getFlashBag()->add(
                    'success',
                    'Agent Truck Info Add Successfully!'
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
     * @Route("/truck/in/{id}", name="truck_in", options={"expose"=true})
     * @param TruckInfo $truckInfo
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @JMS\Secure(roles="ROLE_DELIVERY_MANAGE, ROLE_TRUCK_IN")
     */
    public function truckInAction(TruckInfo $truckInfo)
    {
        $truckInfo->setVehicleIn(new \DateTime());
        $this->getDoctrine()->getRepository('RbsSalesBundle:TruckInfo')->update($truckInfo);

        $this->get('session')->getFlashBag()->add(
            'success',
            'Truck Info Successfully Add'
        );

        return $this->redirect($this->generateUrl('truck_info_in_out_list'));
    }

    /**
     * @Route("/truck/start/{id}", name="truck_start", options={"expose"=true})
     * @param TruckInfo $truckInfo
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @JMS\Secure(roles="ROLE_DELIVERY_MANAGE, ROLE_TRUCK_START")
     */
    public function truckStartAction(TruckInfo $truckInfo)
    {
        $truckInfo->setStartLoad(new \DateTime());
        $this->getDoctrine()->getRepository('RbsSalesBundle:TruckInfo')->update($truckInfo);

        $this->get('session')->getFlashBag()->add(
            'success',
            'Truck Info Successfully Add'
        );

        return $this->redirect($this->generateUrl('truck_info_in_out_list'));
    }

    /**
     * @Route("/truck/finish/{id}", name="truck_finish", options={"expose"=true})
     * @param TruckInfo $truckInfo
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @JMS\Secure(roles="ROLE_DELIVERY_MANAGE, ROLE_TRUCK_FINISH")
     */
    public function truckFinishAction(TruckInfo $truckInfo)
    {
        $truckInfo->setFinishLoad(new \DateTime());
        $this->getDoctrine()->getRepository('RbsSalesBundle:TruckInfo')->update($truckInfo);

        $this->get('session')->getFlashBag()->add(
            'success',
            'Truck Info Successfully Add'
        );

        return $this->redirect($this->generateUrl('truck_info_in_out_list'));
    }

    /**
     * @Route("/truck/out/{id}", name="truck_out", options={"expose"=true})
     * @param TruckInfo $truckInfo
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @JMS\Secure(roles="ROLE_DELIVERY_MANAGE, ROLE_TRUCK_OUT")
     */
    public function truckOutAction(TruckInfo $truckInfo)
    {
        $truckInfo->setVehicleOut(new \DateTime());
        $this->getDoctrine()->getRepository('RbsSalesBundle:TruckInfo')->update($truckInfo);

        $this->get('session')->getFlashBag()->add(
            'success',
            'Truck Info Successfully Add'
        );

        return $this->redirect($this->generateUrl('truck_info_in_out_list'));
    }

    /**
     * @Route("/truck/with/delivery/list", name="truck_with_delivery_list")
     * @Method("GET")
     * @Template()
     * @JMS\Secure(roles="ROLE_DELIVERY_MANAGE, ROLE_TRUCK_IN, ROLE_TRUCK_START, ROLE_TRUCK_FINISH, ROLE_TRUCK_OUT")
     */
    public function truckWithDeliveryListAction()
    {
        $datatable = $this->get('rbs_erp.sales.datatable.truck.with.delivery');
        $datatable->buildDatatable();

        return $this->render('RbsSalesBundle:Truck:truck-with-delivery-index.html.twig', array(
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
        $datatable = $this->get('rbs_erp.sales.datatable.truck.with.delivery');
        $datatable->buildDatatable();

        $getDepoId = $em->getRepository('RbsCoreBundle:Depo')->getDepoId($this->getUser()->getId());
        $getDepoId ? $depoId = $getDepoId[0]['id'] : $depoId = 0;

        $query = $this->get('sg_datatables.query')->getQueryFrom($datatable);
        /** @var QueryBuilder $qb */
        $function = function($qb) use ($depoId)
        {
            if($depoId == 0){
                $qb->join('sales_truck_info.depo', 'd');
                $qb->andWhere('sales_truck_info.vehicleOut IS NULL');
                $qb->andWhere('sales_truck_info.vehicleIn IS NOT NULL');
            }else{
                $qb->join('sales_truck_info.depo', 'd');
                $qb->andWhere('d.id =:depoId');
                $qb->andWhere('sales_truck_info.vehicleOut IS NULL');
                $qb->andWhere('sales_truck_info.vehicleIn IS NOT NULL');
                $qb->setParameters(array('depoId'=>$depoId));
            }
        };
        $query->addWhereAll($function);

        return $query->getResponse();
    }
    
    /**
     * @Route("/truck/{id}/add/with/delivery", name="set_truck_with_delivery", options={"expose"=true})
     * @Template("RbsSalesBundle:Truck:truck-with-delivery.html.twig")
     * @param Request $request
     * @param TruckInfo $truckInfo
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse|Response
     * @JMS\Secure(roles="ROLE_DELIVERY_MANAGE, ROLE_TRUCK_IN, ROLE_TRUCK_START, ROLE_TRUCK_FINISH, ROLE_TRUCK_OUT")
     */
    public function setTruckWithDeliveryAction(Request $request, TruckInfo $truckInfo)
    {
        $form = $this->createForm(new TruckDeliveryForm($this->getUser(), $truckInfo), $truckInfo);

        if ('POST' === $request->getMethod()) {
            $form->handleRequest($request);

            if ($form->isValid()) {

                $delivery = $this->getDoctrine()->getRepository('RbsSalesBundle:Delivery')->find($request->request->get('truck_info')['deliveries']);
                $truckInfo->addOrder($delivery->getOrderRef());
                $truckInfo->setTruckInvoiceAttachedBy($this->getUser());
                $truckInfo->setTruckInvoiceAttachedAt(new \DateTime());

                $this->getDoctrine()->getRepository('RbsSalesBundle:TruckInfo')->update($truckInfo);

                $this->get('session')->getFlashBag()->add(
                    'success',
                    'Set Truck With Delivery Successfully!'
                );
              
                return $this->redirect($this->generateUrl('truck_with_delivery_list'));
            }
        }

        return array(
            'form' => $form->createView()
        );
    }
}