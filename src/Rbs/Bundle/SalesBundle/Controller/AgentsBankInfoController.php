<?php

namespace Rbs\Bundle\SalesBundle\Controller;

use Doctrine\ORM\QueryBuilder;
use Rbs\Bundle\SalesBundle\Entity\Agent;
use Rbs\Bundle\SalesBundle\Entity\AgentsBankInfo;
use Rbs\Bundle\SalesBundle\Form\Type\AgentsBankInfoForm;
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

        $user = $this->getDoctrine()->getManager()->getRepository('RbsSalesBundle:Agent')->findOneBy(array('user' => $this->getUser()));

        $query = $this->get('sg_datatables.query')->getQueryFrom($datatable);
        /** @var QueryBuilder $qb */
        $function = function($qb) use ($user)
        {
            $qb->join('agents_bank_info.agent', 'u');
            $qb->andWhere('u =:user');
            $qb->setParameter('user', $user);
        };
        $query->addWhereAll($function);
        
        return $query->getResponse();
    }

    /**
     * @Route("/orders/my/bank-info", name="orders_my_bank_info", options={"expose"=true})
     * @Template("RbsSalesBundle:Agent:bank_slip_upload.html.twig")
     * @param Request $request
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function agentBankInfoCreateAction(Request $request)
    {
        $agentsBankInfo = new AgentsBankInfo();
        $form = $this->createForm(new AgentsBankInfoForm($this->getUser()), $agentsBankInfo, array(
            'action' => $this->generateUrl('orders_my_bank_info'), 'method' => 'POST',
            'attr' => array('novalidate' => 'novalidate')
        ));

        if ('POST' === $request->getMethod()) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $agent = $this->getDoctrine()->getRepository('RbsSalesBundle:Agent')->findOneBy(array(
                                                             'user' => $this->getUser()->getId()));
                $agentsBankInfo->setAgent($agent);
                $this->getDoctrine()->getManager()->getRepository('RbsSalesBundle:AgentsBankInfo')->create($agentsBankInfo);
                $this->flashMessage('success', 'Bank info add Successfully!');
                return $this->redirect($this->generateUrl('orders_my_home'));
            }
        }

        return array(
            'form' => $form->createView()
        );
    }
}