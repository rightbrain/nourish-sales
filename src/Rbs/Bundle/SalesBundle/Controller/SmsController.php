<?php

namespace Rbs\Bundle\SalesBundle\Controller;

use Doctrine\ORM\QueryBuilder;
use Rbs\Bundle\SalesBundle\Entity\Agent;
use Rbs\Bundle\SalesBundle\Form\Type\AgentBankInfoSmsForm;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use JMS\SecurityExtraBundle\Annotation as JMS;
use Symfony\Component\HttpFoundation\Request;

/**
 * Sms Controller.
 *
 */
class SmsController extends Controller
{
    /**
     * @Route("/sms", name="sms_home")
     * @JMS\Secure(roles="ROLE_ORDER_VIEW")
     */
    public function indexAction()
    {
        $datatable = $this->get('rbs_erp.sales.datatable.sms');
        $datatable->buildDatatable();

        return $this->render('RbsSalesBundle:Sms:index.html.twig', array(
            'datatable' => $datatable
        ));
    }

    /**
     * @Route("/sms_list_ajax", name="sms_list_ajax", options={"expose"=true})
     * @Method("GET")
     * @JMS\Secure(roles="ROLE_ORDER_VIEW")
     */
    public function listAjaxAction()
    {
        $datatable = $this->get('rbs_erp.sales.datatable.sms');
        $datatable->buildDatatable();

        $query = $this->get('sg_datatables.query')->getQueryFrom($datatable);
        /** @var QueryBuilder $qb */
        $function = function($qb)
        {
            $qb->andWhere("sales_sms.status = 'UNREAD'");
        };
        $query->addWhereAll($function);
        return $query->getResponse();
    }

    /**
     * @Route("/sms/readable", name="sms_readable")
     * @JMS\Secure(roles="ROLE_ORDER_VIEW")
     */
    public function indexReadableAction()
    {
        $datatable = $this->get('rbs_erp.sales.datatable.sms');
        $datatable->buildDatatable();

        return $this->render('RbsSalesBundle:Sms:readable.html.twig', array(
            'datatable' => $datatable
        ));
    }
    
    /**
     * @Route("/readable_sms_list_ajax", name="readable_sms_list_ajax", options={"expose"=true})
     * @Method("GET")
     * @JMS\Secure(roles="ROLE_ORDER_VIEW")
     */
    public function readableSmsListAjaxAction()
    {
        $datatable = $this->get('rbs_erp.sales.datatable.sms');
        $datatable->buildDatatable();

        $query = $this->get('sg_datatables.query')->getQueryFrom($datatable);
        /** @var QueryBuilder $qb */
        $function = function($qb)
        {
            $qb->andWhere("sales_sms.status = 'READ'");
        };
        $query->addWhereAll($function);
        return $query->getResponse();
    }

    /**
     * @Route("/agent-bank-info-sms", name="agent_bank_info_sms")
     * @Template("RbsSalesBundle:Sms:agent-bank-info-sms.html.twig")
     * @param Request $request
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function agentBankInfoSms(Request $request)
    {
        $form = $this->createForm(new AgentBankInfoSmsForm());

        if ('POST' === $request->getMethod() && $form->getName() == 'agent_bank_info_sms') {

            return $this->redirect($this->generateUrl('agent_bank_list_sms', array('id' => $request->request->get('agent_bank_info_sms')['agent'])));
        }

        return array(
            'form' => $form->createView()
        );

    }

    /**
     * @Route("/agent-bank-list-sms/{id}", name="agent_bank_list_sms")
     * @Template("RbsSalesBundle:Sms:agent-bank-list-sms.html.twig")
     * @param Request $request
     * @param Agent $agent
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function agentBankListSms(Request $request, Agent $agent)
    {
        $agentBanks = $this->getDoctrine()->getRepository('RbsSalesBundle:AgentBank')->findByAgent($agent);

        if ('POST' === $request->getMethod()) {
            $msg = "Agent Code: " . $request->request->get('agentID') . "; Feed: FX, Chick: CX;";
            $banks = $request->request->get('banks');

            foreach ($banks as $key=>$bank){
                $msg .= " ";
                $agentBank = $this->getDoctrine()->getRepository('RbsSalesBundle:AgentBank')->find($bank);
                $msg .= "(". ($key+1) . ") Bank Name: ". $agentBank->getBank() .", Branch Name: ". $agentBank->getBranch() .", Code:". $agentBank->getCode();
                if(($key+1)< count($banks)){
                    $msg .= ", ";
                }
            }

            $smsSender = $this->get('rbs_erp.sales.service.smssender');
            $smsSender->agentBankInfoSmsAction($msg, $agent->getUser()->getProfile()->getCellphone());

            return $this->redirect($this->generateUrl('agent_bank_info_sms'));
        }

        return array(
            'agent' => $agent,
            'agentBanks' => $agentBanks
        );

    }
}