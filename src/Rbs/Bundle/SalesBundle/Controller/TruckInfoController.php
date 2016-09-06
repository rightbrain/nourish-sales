<?php

namespace Rbs\Bundle\SalesBundle\Controller;

use Doctrine\ORM\QueryBuilder;
use Rbs\Bundle\SalesBundle\Entity\Agent;
use Rbs\Bundle\SalesBundle\Entity\TruckInfo;
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
}