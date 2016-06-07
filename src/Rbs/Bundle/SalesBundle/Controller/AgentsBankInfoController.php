<?php

namespace Rbs\Bundle\SalesBundle\Controller;

use Doctrine\ORM\QueryBuilder;
use Rbs\Bundle\SalesBundle\Entity\Agent;
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
class AgentsBankInfoController extends BaseController
{
    /**
     * @Route("/bank/info/list", name="bank_info_list")
     * @Method("GET")
     * @Template()
     */
    public function indexAction()
    {
        $datatable = $this->get('rbs_erp.sales.datatable.agent.bank.info');
        $datatable->buildDatatable();

        return $this->render('RbsSalesBundle:Agent:my_bank_slip_list.html.twig', array(
            'datatable' => $datatable
        ));
    }

    /**
     * Lists all AgentsBankInfo entities.
     *
     * @Route("/bank_info_list_ajax", name="bank_info_list_ajax", options={"expose"=true})
     * @Method("GET")
     */
    public function listAjaxAction()
    {
        $datatable = $this->get('rbs_erp.sales.datatable.agent.bank.info');
        $datatable->buildDatatable();

        $query = $this->get('sg_datatables.query')->getQueryFrom($datatable);
        /** @var QueryBuilder $qb */
        $function = function($qb)
        {
            $qb->join('agent_bank_info.agent', 'a');
            $qb->join('a.user', 'u');
            $qb->andWhere('u.id =:user');
            $qb->setParameter('user', $this->getUser()->getId());
        };
        
        return $query->getResponse();
    }
}