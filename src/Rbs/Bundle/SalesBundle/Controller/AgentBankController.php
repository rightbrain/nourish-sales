<?php

namespace Rbs\Bundle\SalesBundle\Controller;

use Doctrine\ORM\QueryBuilder;
use Rbs\Bundle\CoreBundle\Entity\Bank;
use Rbs\Bundle\CoreBundle\Entity\BankAccount;
use Rbs\Bundle\CoreBundle\Entity\BankBranch;
use Rbs\Bundle\CoreBundle\Entity\Upload;
use Rbs\Bundle\CoreBundle\Form\Type\UploadForm;
use Rbs\Bundle\SalesBundle\Entity\Agent;
use Rbs\Bundle\SalesBundle\Entity\AgentBank;
use Rbs\Bundle\SalesBundle\Entity\AgentNourishBank;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use JMS\SecurityExtraBundle\Annotation as JMS;
use Rbs\Bundle\SalesBundle\Form\Type\AgentBankForm;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * Agent Bank Controller.
 *
 */
class AgentBankController extends BaseController
{
    /**
     * @Route("/agent/banks", name="agent_banks", options={"expose"=true})
     * @Method("GET")
     * @Template()
     * @JMS\Secure(roles="ROLE_SUPER_ADMIN, ROLE_ADMIN")
     */
    public function indexAction()
    {
        $agents = $this->getDoctrine()->getRepository('RbsSalesBundle:Agent')->agents();
        $datatable = $this->get('rbs_erp.sales.datatable.agent.bank');
        $datatable->buildDatatable();

        return $this->render('RbsSalesBundle:Agent:agentBankList.html.twig', array(
            'agents' => $agents,
            'datatable' => $datatable
        ));
    }

    /**
     * @Route("/agent_banks_list_ajax", name="agent_banks_list_ajax", options={"expose"=true})
     * @Method("GET")
     * @JMS\Secure(roles="ROLE_SUPER_ADMIN, ROLE_ADMIN")
     */
    public function listAjaxAction()
    {
        $datatable = $this->get('rbs_erp.sales.datatable.agent.bank');
        $datatable->buildDatatable();

        $query = $this->get('sg_datatables.query')->getQueryFrom($datatable);
        /** @var QueryBuilder $qb */
        $function = function($qb)
        {
            $qb->join("sales_agent_banks.agent", "a");
            $qb->join("a.user", "u");
            $qb->join("u.profile", "p");
            $qb->addOrderBy('a.id', 'ASC');
            $qb->addOrderBy('sales_agent_banks.id', 'ASC');
            $qb->andWhere("sales_agent_banks.deletedAt IS NULL");
        };
        $query->addWhereAll($function);

        return $query->getResponse();
    }

    /**
     * @Route("/agent/bank/add", name="agent_bank_add")
     * @Template("RbsSalesBundle:Agent:agentBankForm.html.twig")
     * @param Request $request
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse|Response
     * @JMS\Secure(roles="ROLE_SUPER_ADMIN, ROLE_ADMIN")
     */
    public function createAction(Request $request)
    {
        $agentBank = new AgentBank();
        $form = $this->createForm(new AgentBankForm(null), $agentBank);

        if ('POST' === $request->getMethod()) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $agentBanks = $this->getDoctrine()->getRepository('RbsSalesBundle:AgentBank')->getByAgent($request->request->all()['agent_bank'] ["agent"]);
                $agentBank->setCode($this->unique_id($agentBanks));
                $this->getDoctrine()->getRepository('RbsSalesBundle:AgentBank')->create($agentBank);
                $this->get('session')->getFlashBag()->add('success','Agent Bank Successfully!');
                return $this->redirect($this->generateUrl('agent_banks'));
            }
        }

        return array(
            'form' => $form->createView(),
        );
    }

    function unique_id($agentBanks) {
        $val=1;
        foreach ($agentBanks as $agentBank){
            $val++;
        }
        return $val;
    }

    /**
     * @Route("/agent/{id}/bank/add", name="agent_bank_individual_add")
     * @Template("RbsSalesBundle:Agent:agentBankForm.html.twig")
     * @param Request $request
     * @param Agent $agent
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse|Response
     * @JMS\Secure(roles="ROLE_SUPER_ADMIN, ROLE_ADMIN")
     */
    public function createIndividualAction(Request $request, Agent $agent)
    {
        $agentBank = new AgentBank();
        $form = $this->createForm(new AgentBankForm($agent), $agentBank);

        if ('POST' === $request->getMethod()) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $agentBanks = $this->getDoctrine()->getRepository('RbsSalesBundle:AgentBank')->getByAgent($agent->getId());
                $agentBank->setCode($this->unique_id($agentBanks, 2));
                $this->getDoctrine()->getRepository('RbsSalesBundle:AgentBank')->create($agentBank);
                $this->get('session')->getFlashBag()->add('success','Agent Bank Successfully!');
                return $this->redirect($this->generateUrl('agent_banks'));
            }
        }

        return array(
            'form' => $form->createView(),
        );
    }

    /**
     * @Route("/agent/bank/{id}/update", name="agent_bank_update", options={"expose"=true})
     * @Template("RbsSalesBundle:Agent:agentBankForm.html.twig")
     * @param Request $request
     * @param AgentBank $agentBank
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse|Response
     * @JMS\Secure(roles="ROLE_SUPER_ADMIN, ROLE_ADMIN")
     */
    public function updateAction(Request $request, AgentBank $agentBank)
    {
        $form = $this->createForm(new AgentBankForm($agentBank->getAgent()), $agentBank);

        if ('POST' === $request->getMethod()) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $this->getDoctrine()->getRepository('RbsSalesBundle:AgentBank')->update($agentBank);
                $this->get('session')->getFlashBag()->add('success','Agent Bank Updated Successfully!');
                return $this->redirect($this->generateUrl('agent_banks'));
            }
        }

        return array(
            'form' => $form->createView(),
        );
    }

    /**
     * @Route("/agents/nourish/banks", name="agent_nourish_banks", options={"expose"=true})
     * @Method("GET")
     * @Template()
     * @JMS\Secure(roles="ROLE_SUPER_ADMIN, ROLE_ADMIN")
     */
    public function agentNourishBanksAction()
    {
        $datatable = $this->get('rbs_erp.sales.datatable.agent.nourish.bank');
        $datatable->buildDatatable();

        return $this->render('RbsSalesBundle:Agent:agentNourishBankList.html.twig', array(
            'datatable' => $datatable
        ));
    }

    /**
     * @Route("/agent_nourish_banks_list_ajax", name="agent_nourish_banks_list_ajax", options={"expose"=true})
     * @Method("GET")
     * @JMS\Secure(roles="ROLE_SUPER_ADMIN, ROLE_ADMIN")
     */
    public function agentNourishBankListAjaxAction()
    {
        $datatable = $this->get('rbs_erp.sales.datatable.agent.nourish.bank');
        $datatable->buildDatatable();

        $query = $this->get('sg_datatables.query')->getQueryFrom($datatable);
        /** @var QueryBuilder $qb */
        $function = function($qb)
        {
            $qb->join("sales_agent_nourish_banks.agent", "a");
            $qb->join("a.user", "u");
            $qb->join("u.profile", "p");
            $qb->addOrderBy('a.id', 'ASC');
        };
        $query->addWhereAll($function);

        return $query->getResponse();
    }

    /**
     * @Route("/agent_nourish_banks/delete/{id}", name="agent_nourish_banks_delete", options={"expose"=true})
     * @param AgentNourishBank $agentNourishBank
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @JMS\Secure(roles="ROLE_SUPER_ADMIN, ROLE_ADMIN")
     */
    public function agentNourishBankDeleteAction(AgentNourishBank $agentNourishBank)
    {
        $this->getDoctrine()->getRepository('RbsSalesBundle:AgentNourishBank')->delete($agentNourishBank);

        $this->get('session')->getFlashBag()->add(
            'success',
            'Agents Nourish Bank Deleted Successfully'
        );

        return $this->redirect($this->generateUrl('agent_nourish_banks'));
    }

    /**
     * @Route("/agent/bank/import", name="agent_bank_import")
     * @Template("RbsSalesBundle:Agent:agent_bank_import.html.twig")
     * @param Request $request
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @JMS\Secure(roles="ROLE_USER_CREATE, ROLE_ADMIN")
     */
    public function agentBankImprotAndNourishBankAsignAction(Request $request)
    {
        set_time_limit(0);
        $upload = new Upload();
        $form = $this->createForm(new UploadForm(), $upload);
        $em = $this->getDoctrine()->getManager();
        if ('POST' === $request->getMethod()) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $file = $upload->getFile();
                $fileName = md5(uniqid()).'_bank.csv';
                $file->move(
                    $this->getParameter('brochures_directory'),
                    $fileName
                );
                $upload->setFile($fileName);

                $file = $this->get('request')->getSchemeAndHttpHost().'/uploads/sales/csv-import/'.$fileName;

                $data = $this->getCSVFileData($file);
                $i = 0;
                $j = 1;
                foreach ($data as $row) {
                    $agentBank = new AgentBank();

                    if ($i == 0) {$i++; continue;}

                    $agentCode = $row[0];
                    $fullName = $row[1];
                    $agentBankName = $row[2];
                    $agentBankBranch = $row[3];
                    $cellPhone = $row[4];
                    $nourishBankCode = $row[5];
                    $nourishBankAccountName = $row[6];
                    $nourishBankName = $row[7];
                    $nourishBankBrnachName = $row[8];

                    if($agentCode=='' || $nourishBankCode==''){
                        continue;
                    }

                    $existingNourishBank = $this->getDoctrine()->getRepository('RbsCoreBundle:BankAccount')->findOneBy(array('code'=>$nourishBankCode));
                    if(!$existingNourishBank){
                        $bank= new Bank();
                        $branch = new BankBranch();
                        $bankAccount = new BankAccount();
                        $bank->setName($nourishBankName);
                        $em->persist($bank);
                        $branch->setName($nourishBankBrnachName);
                        $branch->setBank($bank);
                        $em->persist($branch);

                        $bankAccount->setName($nourishBankAccountName);
                        $bankAccount->setBranch($branch);
                        $bankAccount->setCode($nourishBankCode);
                        $em->persist($bankAccount);

                        $em->flush();
                    }

                    $existingAgentNourishBank = $this->getDoctrine()->getRepository('RbsSalesBundle:AgentNourishBank')->getAgentNourishBankByAgentCodeAndBankCode($agentCode, $nourishBankCode);
                    $agent = $this->getDoctrine()->getRepository('RbsSalesBundle:Agent')->findOneBy(array('agentID'=>$agentCode));
                    if($nourishBankCode!='' && !$existingAgentNourishBank && $agent){

                        $bankAccount = $this->getDoctrine()->getRepository('RbsCoreBundle:BankAccount')->findOneBy(array('code'=>$nourishBankCode));

                        $agentNourishBank = new AgentNourishBank();
                        $agentNourishBank->setAgent($agent);
                        $agentNourishBank->setAccount($bankAccount);
                        $em->persist($agentNourishBank);
                        $em->flush();
                    }

                    $existingAgentBank= $this->getDoctrine()->getRepository('RbsSalesBundle:AgentBank')->findOneBy(array('agent'=>$agent,'bank'=>$agentBankName,'branch'=>$agentBankBranch));
                    if(!$existingAgentBank && $agent){
                        $agentBanks = $this->getDoctrine()->getRepository('RbsSalesBundle:AgentBank')->getByAgent($agent);
                        $agentBank->setCode($this->unique_id($agentBanks));
                        $agentBank->setCellphone('+88'.$cellPhone);
                        $agentBank->setBank($agentBankName);
                        $agentBank->setBranch($agentBankBranch);
                        $agentBank->setAgent($agent);
                        $this->getDoctrine()->getRepository('RbsSalesBundle:AgentBank')->create($agentBank);
                    }

                    $j++;
                }

                $this->get('session')->getFlashBag()->add(
                    'success',
                    'Agent Bank and Nourish bank assigned Successfully'
                );

                return $this->redirect($this->generateUrl('agent_bank_info_sms'));
            }
        }

        return array(
            'form' => $form->createView()
        );
    }



    public function getCSVFileData($webPath) {
        $fileData = array();
        $file = fopen($webPath,"r");
        while(! feof($file)) {
            $fileData[] = fgetcsv($file, 1024);
        }
        fclose($file);
        return $fileData;
    }


}