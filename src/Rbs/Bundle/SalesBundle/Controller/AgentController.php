<?php

namespace Rbs\Bundle\SalesBundle\Controller;

use Doctrine\ORM\QueryBuilder;
use Rbs\Bundle\CoreBundle\Entity\Upload;
use Rbs\Bundle\CoreBundle\Form\Type\UploadForm;
use Rbs\Bundle\SalesBundle\Entity\Agent;
use Rbs\Bundle\SalesBundle\Entity\AgentDoc;
use Rbs\Bundle\SalesBundle\Entity\Order;
use Rbs\Bundle\SalesBundle\Entity\Payment;
use Rbs\Bundle\SalesBundle\Form\Type\AgentDocForm;
use Rbs\Bundle\SalesBundle\Form\Type\AgentUpdateForm;
use Rbs\Bundle\SalesBundle\Form\Type\OrderForm;
use Rbs\Bundle\UserBundle\Entity\Group;
use Rbs\Bundle\UserBundle\Entity\Profile;
use Rbs\Bundle\UserBundle\Entity\User;
use Rbs\Bundle\UserBundle\Form\Type\UserUpdatePasswordForm;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use JMS\SecurityExtraBundle\Annotation as JMS;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Agent Controller.
 *
 */
class AgentController extends BaseController
{
    /**
     * @Route("/agents", name="agents_home")
     * @Method("GET")
     * @Template()
     * @JMS\Secure(roles="ROLE_AGENT_VIEW, ROLE_AGENT_CREATE")
     */
    public function indexAction()
    {
        $agents = $this->getDoctrine()->getRepository('RbsSalesBundle:Agent')->agents();
        $datatable = $this->get('rbs_erp.sales.datatable.agent');
        $datatable->buildDatatable();

        return $this->render('RbsSalesBundle:Agent:index.html.twig', array(
            'agents' => $agents,
            'datatable' => $datatable
        ));
    }

    /**
     * @Route("/agents_list_ajax", name="agents_list_ajax", options={"expose"=true})
     * @Method("GET")
     * @JMS\Secure(roles="ROLE_AGENT_VIEW, ROLE_AGENT_CREATE")
     */
    public function listAjaxAction()
    {
        $user= $this->getUser();
        $datatable = $this->get('rbs_erp.sales.datatable.agent');
        $datatable->buildDatatable();

        $query = $this->get('sg_datatables.query')->getQueryFrom($datatable);
        /** @var QueryBuilder $qb */
        $function = function($qb) use ($user)
        {
            $qb->join("sales_agents.user", "u");
            $qb->andWhere("u.userType =:AGENT");
            $qb->andWhere("sales_agents.deletedAt IS NULL");
            $qb->setParameter("AGENT", User::AGENT);

            if(in_array('ROLE_CHICK_ORDER_MANAGE', $user->getRoles())){
                $qb->andWhere('sales_agents.agentType = :type');
                $qb->setParameter('type',Agent::AGENT_TYPE_CHICK);
            }
            if (in_array('ROLE_FEED_ORDER_MANAGE', $user->getRoles())){
                $qb->andWhere('sales_agents.agentType IS NULL OR sales_agents.agentType = :type');
                $qb->setParameter('type',Agent::AGENT_TYPE_FEED);
            }
            if (in_array('ROLE_CHICK_ORDER_MANAGE', $user->getRoles()) && in_array('ROLE_FEED_ORDER_MANAGE', $user->getRoles())){
                /*$qb->andWhere($qb->expr()->orX(
                    $qb->expr()->eq('sales_agents.agentType', "'".Agent::AGENT_TYPE_FEED."'"),
                    $qb->expr()->eq('sales_agents.agentType', "'".Agent::AGENT_TYPE_CHICK."'")
                ));*/
//                $qb->setParameter('fType',Agent::AGENT_TYPE_FEED);
//                $qb->setParameter('cType',Agent::AGENT_TYPE_CHICK);

                $qb->andWhere('sales_agents.agentType = :fType');
                $qb->orWhere('sales_agents.agentType = :cType');
                $qb->setParameter('fType',Agent::AGENT_TYPE_FEED);
                $qb->setParameter('cType',Agent::AGENT_TYPE_CHICK);
            }

        };
        $query->addWhereAll($function);

        if ($this->isGranted('ROLE_AGENT')) {
            $query->getQuery()->andWhere('agents.sr = :agent')->setParameter('sr', $this->getUser());
        }

        return $query->getResponse();
    }

    /**
     * @Route("/agent/update/{id}", name="agent_update", options={"expose"=true})
     * @Template("RbsSalesBundle:Agent:update.html.twig")
     * @param Request $request
     * @param Agent $agent
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse|Response
     * @JMS\Secure(roles="ROLE_AGENT_VIEW, ROLE_AGENT_CREATE")
     */
    public function updateAction(Request $request, Agent $agent)
    {
        $profile = $agent->getUser()->getProfile();
        $form = $this->createForm(new AgentUpdateForm($agent->isOpeningBalanceFlag()), $agent, array(
            'attr' => array('novalidate' => 'novalidate')
        ));

        if ('POST' === $request->getMethod()) {
            $form->handleRequest($request);
            if ($form->isValid()) {

                if(!$agent->isOpeningBalanceFlag()){
                    $em = $this->getDoctrine()->getManager();
                    $payment = new Payment();

                    $payment->setAgent($agent);
                    $payment->setAmount($agent->getOpeningBalance());
                    $payment->setPaymentMethod(Payment::PAYMENT_METHOD_OPENING_BALANCE);
                    $payment->setRemark('Agents opening balance.');
//                    $payment->setDepositDate(date("Y-m-d"));
                    $payment->setTransactionType($agent->getOpeningBalanceType());
                    $payment->setVerified(true);
                    $em->getRepository('RbsSalesBundle:Payment')->create($payment);
                    $agent->setOpeningBalanceFlag(true);
                }
                if($agent->getAgentType() == Agent::AGENT_TYPE_CHICK){
                    $agent->setChickAgentID($agent->getAgentCodeForDatatable());
                }else{
                    $agent->setAgentID($agent->getAgentCodeForDatatable());
                }

                $this->getDoctrine()->getRepository('RbsSalesBundle:Agent')->update($agent);

                if($agent->getAgentType() == Agent::AGENT_TYPE_CHICK){
                    $profile->setCellphoneForChick($profile->getCellphoneForMapping());
                }else{
                    $profile->setCellphone($profile->getCellphoneForMapping());
                }
                $this->getDoctrine()->getRepository('RbsUserBundle:Profile')->update($profile);
                $this->get('session')->getFlashBag()->add('success','User Updated Successfully!');
                return $this->redirect($this->generateUrl('agents_home'));
            }
        }

        return array(
            'form' => $form->createView(),
            'agent' => $agent
        );
    }

    /**
     * @Route("/agent/update/password/{id}", name="agent_update_password", options={"expose"=true})
     * @Template("RbsSalesBundle:Agent:update.password.html.twig")
     * @param Request $request
     * @param User $user
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     * @JMS\Secure(roles="ROLE_AGENT_VIEW, ROLE_AGENT_CREATE")
     */
    public function updatePasswordAction(Request $request, User $user)
    {
        $form = $this->createForm(new UserUpdatePasswordForm(), $user);

        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $user->setPassword($form->get('plainPassword')->getData());
                $user->setPlainPassword($form->get('plainPassword')->getData());

                $this->getDoctrine()->getRepository('RbsUserBundle:User')->update($user);
                $this->get('session')->getFlashBag()->add('notice','Password Successfully Change');
                return $this->redirect($this->generateUrl('homepage'));
            }
        }

        return array(
            'form' => $form->createView()
        );
    }

    /**
     * @Route("/agent/details/{id}", name="agent_details", options={"expose"=true})
     * @Template()
     * @param Agent $agent
     * @return \Symfony\Component\HttpFoundation\Response
     * @JMS\Secure(roles="ROLE_AGENT_VIEW, ROLE_AGENT_CREATE")
     */
    public function detailsAction(Agent $agent)
    {
        $this->checkViewDetailAccess($agent);

        $data = array();
        $data['agent'] = $agent->getId();
        $agentBanks = $this->getDoctrine()->getRepository('RbsSalesBundle:AgentBank')->findByAgent($agent);

        $agentDebitLaserTotal = $this->getDoctrine()->getRepository('RbsSalesBundle:Payment')->getAgentDebitLaserTotal($data);
        $agentCreditLaserTotal = $this->getDoctrine()->getRepository('RbsSalesBundle:Payment')->getAgentCreditLaserTotal($data);
        $currentBalance = $agentCreditLaserTotal - $agentDebitLaserTotal;
        
        return $this->render('RbsSalesBundle:Agent:details.html.twig', array(
            'agent' => $agent,
            'agentBanks' => $agentBanks,
            'agentCurrentBalance' => $currentBalance
        ));
    }

    protected function checkViewDetailAccess(Agent $agent)
    {
        if ($this->isGranted('ROLE_AGENT')) {
            if ($agent->getSr() != $this->getUser()) {
                throw new AccessDeniedException('Access Denied');
            }
        }
    }

    /**
     * @Route("/agent/delete/{id}", name="agent_delete", options={"expose"=true})
     * @param Agent $agent
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @JMS\Secure(roles="ROLE_AGENT_VIEW, ROLE_AGENT_CREATE")
     */
    public function deleteAction(Agent $agent)
    {
        if($agent->getUser()->getProfile()->getPath()) {
            $agent->getUser()->getProfile()->removeFile($agent->getUser()->getProfile()->getPath());
        }

        $this->getDoctrine()->getManager()->remove($agent);
        $this->getDoctrine()->getManager()->remove($agent->getUser());
        $this->getDoctrine()->getManager()->flush();

        $this->get('session')->getFlashBag()->add('success','Agent Successfully Deleted');
        return $this->redirect($this->generateUrl('agents_home'));
    }

    /**
     * @Route("find_agent_ajax", name="find_agent_ajax", options={"expose"=true})
     * @param Request $request
     * @return Response
     * @JMS\Secure(roles="ROLE_AGENT, ROLE_DEPO_USER, ROLE_ORDER_VIEW, ROLE_AGENT_VIEW, ROLE_AGENT_CREATE")
     */
    public function findAgentAction(Request $request)
    {
        $agentId = $request->request->get('agent');
        $agentRepo = $this->getDoctrine()->getRepository('RbsSalesBundle:Agent');
        $agent = $agentRepo->find($agentId);

        $order = new Order();
        $order->setAgent($agent);
        $form = $this->createForm(new OrderForm(0), $order);
        $prototype = $this->renderView('@RbsSales/Order/_itemTypePrototype.html.twig', array('form' => $form->createView()));

        return new JsonResponse(array('item_type_prototype' => $prototype));
    }

    /**
     * @Route("/payment/agent-search", name="agent_search", options={"expose"=true})
     * @param Request $request
     * @return JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse
     * @JMS\Secure(roles="ROLE_AGENT_VIEW, ROLE_AGENT_CREATE")
     */
    public function getAgents(Request $request)
    {
        $qb = $this->agentRepository()->createQueryBuilder('c');
        $qb->join('c.user', 'u');
        $qb->join('u.profile', 'p');
        $qb->select("u.id, CONCAT(c.agentCodeForDatatable, ' - ', p.fullName) AS text");
        $qb->setMaxResults(100);
//        $qb->where('c.agentID IS NOT NULL');
        if ($this->isGranted('ROLE_FEED_ORDER_MANAGE')) {
            if ($q = $request->query->get('q')) {
                $qb->andWhere("c.agentID LIKE '%{$q}%' OR p.fullName LIKE '%{$q}%'");
            }
        }
        if ($this->isGranted('ROLE_CHICK_ORDER_MANAGE')) {
            if ($q = $request->query->get('q')) {
                $qb->andWhere("c.chickAgentID LIKE '%{$q}%' OR p.fullName LIKE '%{$q}%'");
            }
        }
        if ($this->isGranted('ROLE_CHICK_ORDER_MANAGE') && $this->isGranted('ROLE_FEED_ORDER_MANAGE')) {
            if ($q = $request->query->get('q')) {
                $qb->andWhere("c.agentID LIKE '%{$q}%' OR c.chickAgentID LIKE '%{$q}%' OR p.fullName LIKE '%{$q}%'");
            }
        }

        return new JsonResponse($qb->getQuery()->getResult());
    }

    /**
     * @Route("/my/doc", name="my_doc")
     * @Method("GET")
     * @Template()
     * @JMS\Secure(roles="ROLE_AGENT")
     */
    public function myDocAction()
    {
        $agent = $this->getDoctrine()->getRepository('RbsSalesBundle:Agent')
            ->findOneBy(array('user' => $this->getUser()->getId()));
        $docs = $this->getDoctrine()->getRepository('RbsSalesBundle:AgentDoc')
            ->getAllFileForLogInAgent($agent->getId());

        return $this->render('RbsSalesBundle:Agent:my-doc.html.twig', array(
            'agent' => $agent,
            'docs' => $docs,
        ));
    }

    /**
     * @Route("/my/doc/add", name="my_doc_add")
     * @Template("RbsSalesBundle:Agent:my-doc-upload.html.twig")
     * @param Request $request
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @JMS\Secure(roles="ROLE_AGENT")
     */
    public function agentBankInfoCreateAction(Request $request)
    {
        $agentDoc = new AgentDoc();
        $form = $this->createForm(new AgentDocForm($this->getUser()), $agentDoc, array(
            'action' => $this->generateUrl('my_doc_add'), 'method' => 'POST',
            'attr' => array('novalidate' => 'novalidate')
        ));

        if ('POST' === $request->getMethod()) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $agent = $this->getDoctrine()->getRepository('RbsSalesBundle:Agent')
                    ->findOneBy(array('user' => $this->getUser()->getId()));
                $agentDoc->setAgent($agent);
                $this->getDoctrine()->getManager()->getRepository('RbsSalesBundle:AgentDoc')->create($agentDoc);
                $this->flashMessage('success', 'Agent Doc add Successfully!');
                return $this->redirect($this->generateUrl('my_doc'));
            }
        }

        return array(
            'form' => $form->createView()
        );
    }

    /**
     * @Route("/agent/import", name="agent_import")
     * @Template("RbsSalesBundle:Agent:agent_import.html.twig")
     * @param Request $request
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @JMS\Secure(roles="ROLE_USER_CREATE, ROLE_ADMIN")
     */
    public function agentImportAction(Request $request)
    {
        set_time_limit(0);
        $group = $this->getDoctrine()->getRepository('RbsUserBundle:Group')->findOneBy(array('name'=>'Agent User'));
        $upload = new Upload();
        $form = $this->createForm(new UploadForm(), $upload);

        if ('POST' === $request->getMethod()) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $agent_type = $request->request->get('agent_type');
                $file = $upload->getFile();
                $fileName = md5(uniqid()).'.csv';
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
                    $time = time();
                    $user = new User();

                    $profile = new Profile();
                    $agent = new Agent();

                    if ($i == 0) {$i++; continue;}

                    $zilla = $row[0];
                    $upazilla = $row[1];
                    $username = $row[2];
                    $email = $row[3];
                    $password = $row[4];
                    $itemType = $row[5];
                    $depot = $row[6];
                    $openingBalance = $row[7];
                    $openingBalanceType = $row[8];
                    $agentCode = $row[9];
                    $fullName = $row[10];
                    $cellPhone = $row[11];
                    $designation = $row[12];
                    $address = $row[13];

                    if($zilla==''||$upazilla==''||$username==''||$email==''){
                        continue;
                    }

                    $exitingAgent = $this->getDoctrine()->getRepository('RbsSalesBundle:Agent')->findOneBy(array('agentID'=>$agentCode));

                    if($exitingAgent && $agent_type=='FEED'){
                        continue;
                    }

                    $exitingChickAgent = $this->getDoctrine()->getRepository('RbsSalesBundle:Agent')->findOneBy(array('chickAgentID'=>$agentCode));

                    if($exitingChickAgent && $agent_type=='CHICK'){
                        continue;
                    }
                    $zillaObj = $this->getDoctrine()->getRepository('RbsCoreBundle:Location')->findOneBy(array('name'=>$zilla,'level'=>4));

                    $upazillaObj = $this->getDoctrine()->getRepository('RbsCoreBundle:Location')->findOneBy(array('name'=>$upazilla,'level'=>5,'parentId'=>$zillaObj?$zillaObj->getId():''));

                    $depotObj = $this->getDoctrine()->getRepository('RbsCoreBundle:Depo')->findOneBy(array('name'=>$depot));


                    if($agent_type=='CHICK'){

                        $exitingUser = $this->getDoctrine()->getRepository('RbsUserBundle:User')->findOneBy(array('email'=>'chick_'.$email));
                        $exitingUserByusername = $this->getDoctrine()->getRepository('RbsUserBundle:User')->findOneBy(array('username'=>'chick_'.$username));
                        $exitingProfileByCellphone = $this->getDoctrine()->getRepository('RbsUserBundle:Profile')->findOneBy(array('cellphoneForChick'=>'+88'.$cellPhone));
                    }else{

                        $exitingUser = $this->getDoctrine()->getRepository('RbsUserBundle:User')->findOneBy(array('email'=>$email));
                        $exitingUserByusername = $this->getDoctrine()->getRepository('RbsUserBundle:User')->findOneBy(array('username'=>$username));
                        $exitingProfileByCellphone = $this->getDoctrine()->getRepository('RbsUserBundle:Profile')->findOneBy(array('cellphone'=>'+88'.$cellPhone));
                    }

                    if($exitingUser || $exitingUserByusername || $exitingProfileByCellphone){

                        continue;

                    }

                    $user->setEnabled(true);
                    $user->setRoles(array("ROLE_AGENT"));
                    $user->setUserType( User::AGENT);
//                    $user->addGroup(array(1));

                    $user->setZilla($zillaObj?$zillaObj:null);
                    $user->setUpozilla($upazillaObj? $upazillaObj:null);
                    if($agent_type=='CHICK'){
                        $user->setUsername('chick_'.$username);
                        $user->setEmail('chick_'.$email);
                    }else{

                        $user->setUsername($username);
                        $user->setEmail($email);
                    }
                    $user->setPlainPassword($password);
                    $user->addGroup($group);
                    $this->getDoctrine()->getManager()->persist($user);
                    $profile->setUser($user);
                    if($agent_type=='FEED') {
                        $profile->setCellphone('+88' . $cellPhone);
                        $profile->setCellphoneForChick($time.'_'.$j);
                    }
                    if ($agent_type=='CHICK'){
                        $profile->setCellphone($time.'_'.$j);
                        $profile->setCellphoneForChick('+88' . $cellPhone);
                    }
                    $profile->setCellphoneForMapping('+88' . $cellPhone);
                    $profile->setFullName($fullName);
                    $profile->setAddress($address);
                    $profile->setDesignation($designation);
                    $this->getDoctrine()->getManager()->persist($profile);
                    $agent->setUser($user);
                    $agent->setItemType($this->getDoctrine()->getRepository('RbsCoreBundle:ItemType')->find($itemType));

                    $agent->setDepo($depotObj&&$agent_type=='FEED'?$depotObj:null);

                    $agent->setDepotForChick($depotObj&&$agent_type=='CHICK'?$depotObj:null);

                    $agent->setAgentID($agentCode&&$agent_type=='FEED'?$agentCode:null);
                    $agent->setChickAgentID($agentCode&&$agent_type=='CHICK'?$agentCode:null);

                    $agent->setAgentCodeForDatatable($agentCode);

                    $agent->setAgentType($agent_type=='CHICK'?Agent::AGENT_TYPE_CHICK:Agent::AGENT_TYPE_FEED);

                    $this->getDoctrine()->getRepository('RbsSalesBundle:Agent')->create($agent);

                    if($openingBalance!='' && in_array($openingBalanceType,array('CR','DR'))){
                        $payment = new Payment();

                        $agent->setOpeningBalance($openingBalance);
                        $agent->setOpeningBalanceType($openingBalanceType);
                        $agent->setOpeningBalanceFlag(true);

                        $payment->setAgent($agent);
                        $payment->setAmount($agent->getOpeningBalance());
                        $payment->setPaymentMethod(Payment::PAYMENT_METHOD_OPENING_BALANCE);
                        $payment->setRemark('Agents opening balance.');
                        $payment->setFxCx($agent_type=='CHICK'?'CK':'FD');

                        $payment->setTransactionType($agent->getOpeningBalanceType());
                        $payment->setVerified(true);
                        $this->getDoctrine()->getRepository('RbsSalesBundle:Payment')->create($payment);


                    }

                    $j++;
                }

                $this->get('session')->getFlashBag()->add(
                    'success',
                    'User Created Successfully'
                );

                return $this->redirect($this->generateUrl('agents_home'));
            }
        }

        return array(
            'form' => $form->createView()
        );
    }
    /**
     * @Route("/agent/username/change", name="agent_username_change")
     * @param Request $request
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @JMS\Secure(roles="ROLE_USER_CREATE, ROLE_ADMIN")
     */
    public function changeAgentUserName(){
        set_time_limit(0);
        $agents = $this->getDoctrine()->getRepository('RbsSalesBundle:Agent')->agents();
        /** @var Agent $agent */
        foreach ($agents as $agent){

            $user = $this->getDoctrine()->getRepository('RbsUserBundle:User')->find($agent->getUser()->getId());
            if($agent->getAgentID()==$user->getUsername()){
                $user->setUsername(ltrim($user->getProfile()->getCellphone(),"+88"));
                $this->getDoctrine()->getRepository('RbsUserBundle:User')->update($user);

            }

        }
        $this->get('session')->getFlashBag()->add(
            'success',
            'Agent Username changed successfully'
        );
        return $this->redirect($this->generateUrl('agents_home'));
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