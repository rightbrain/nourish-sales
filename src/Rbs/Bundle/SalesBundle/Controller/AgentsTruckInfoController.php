<?php

namespace Rbs\Bundle\SalesBundle\Controller;

use Doctrine\ORM\QueryBuilder;
use Rbs\Bundle\SalesBundle\Entity\AgentsTruckInfo;
use Rbs\Bundle\SalesBundle\Form\Type\AgentsTruckInfoForm;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use JMS\SecurityExtraBundle\Annotation as JMS;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * AgentsBankInfo Controller.
 *
 */
class AgentsTruckInfoController extends BaseController
{
    /**
     * @Route("/truck/info/list", name="truck_info_list")
     * @Method("GET")
     * @Template()
     * @JMS\Secure(roles="ROLE_ADMIN")
     */
    public function indexAction()
    {
        $datatable = $this->get('rbs_erp.sales.datatable.agent.truck.info');
        $datatable->buildDatatable();

        return $this->render('RbsSalesBundle:AgentTruck:index.html.twig', array(
            'datatable' => $datatable
        ));
    }

    /**
     * @Route("/truck_info_list_ajax", name="truck_info_list_ajax", options={"expose"=true})
     * @Method("GET")
     * @JMS\Secure(roles="ROLE_ADMIN")
     */
    public function listAjaxAction()
    {
        $datatable = $this->get('rbs_erp.sales.datatable.agent.truck.info');
        $datatable->buildDatatable();

        $query = $this->get('sg_datatables.query')->getQueryFrom($datatable);
        /** @var QueryBuilder $qb */
        $function = function($qb)
        {
            $qb->join('sales_agents_truck_info.agent', 'u');
        };
        $query->addWhereAll($function);
        
        return $query->getResponse();
    }
    /**
     * @Route("/truck/info/my/list", name="truck_info_my_list")
     * @Method("GET")
     * @Template()
     * @JMS\Secure(roles="ROLE_AGENT")
     */
    public function myIndexAction()
    {
        $datatable = $this->get('rbs_erp.sales.datatable.agent.my.truck.info');
        $datatable->buildDatatable();

        return $this->render('RbsSalesBundle:AgentTruck:index.html.twig', array(
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
        $datatable = $this->get('rbs_erp.sales.datatable.agent.my.truck.info');
        $datatable->buildDatatable();

        $query = $this->get('sg_datatables.query')->getQueryFrom($datatable);
        /** @var QueryBuilder $qb */
        $function = function($qb)
        {
            $qb->join('sales_agents_truck_info.agent', 'u');
            $qb->andWhere('u =:user');
            $qb->setParameter('user', $this->getUser());
        };
        $query->addWhereAll($function);
        
        return $query->getResponse();
    }
    
    /**
     * @Route("/agent/truck/info/add", name="agent_truck_info_add")
     * @Template("RbsSalesBundle:AgentTruck:form.html.twig")
     * @param Request $request
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse|Response
     * @JMS\Secure(roles="ROLE_AGENT")
     */
    public function addAction(Request $request)
    {
        $agentTruckInfo = new AgentsTruckInfo();

        $form = $this->createForm(new AgentsTruckInfoForm($this->getUser()), $agentTruckInfo);

        if ('POST' === $request->getMethod()) {
            $form->handleRequest($request);

            if ($form->isValid()) {

                $agentTruckInfo->setAgent($this->getUser());
                $this->getDoctrine()->getRepository('RbsSalesBundle:AgentsTruckInfo')->create($agentTruckInfo);

                $this->get('session')->getFlashBag()->add(
                    'success',
                    'Agent Truck Info Add Successfully!'
                );

                return $this->redirect($this->generateUrl('truck_info_my_list'));
            }
        }

        return array(
            'form' => $form->createView()
        );
    }
}