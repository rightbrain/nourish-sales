<?php

namespace Rbs\Bundle\SalesBundle\Controller;

use Rbs\Bundle\SalesBundle\Entity\ChickenSetForAgent;
use Rbs\Bundle\SalesBundle\Form\Type\ChickenTypeSetForm;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use JMS\SecurityExtraBundle\Annotation as JMS;
use Symfony\Component\HttpFoundation\Request;

/**
 * ChickenTypeSet Controller.
 *
 */
class ChickenTypeSetController extends BaseController
{
    /**
     * @Route("/chicken/type/set/list", name="chicken_type_set_list")
     * @return \Symfony\Component\HttpFoundation\Response
     * @JMS\Secure(roles="ROLE_SR_GROUP")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        $agents = $em->getRepository('RbsSalesBundle:ChickenSetForAgent')->findAgentsUsingParentId($this->getUser()->getId());
        $myChickenTargets = $em->getRepository('RbsCoreBundle:ChickenSet')->findBy(array(
            'location' => $this->getUser()->getZilla()
        ));

        return $this->render('RbsSalesBundle:DamageGood:chicken-type.html.twig', array(
            'agents' => $agents,
            'myChickenTargets' => $myChickenTargets
        ));
    }

    /**
     * @Route("/chicken/type/add/{id}", name="chicken_type_add")
     * @Template("RbsSalesBundle:DamageGood:add.html.twig")
     * @param ChickenSetForAgent $chickenSetForAgent
     * @return array
     */
    public function addAction(ChickenSetForAgent $chickenSetForAgent)
    {
        return array(
            'chickenSetForAgent' => $chickenSetForAgent
        );
    }

    /**
     * @Route("/chicken/type/save/{id}", name="chicken_type_save")
     * @param Request $request
     * @param ChickenSetForAgent $chickenSetForAgent
     * @return array
     */
    public function saveAction(Request $request, ChickenSetForAgent $chickenSetForAgent)
    {
        $chickenSetForAgent->setQuantity($request->request->get('quantity'));
        $this->getDoctrine()->getRepository('RbsSalesBundle:ChickenSetForAgent')->update($chickenSetForAgent);
        $this->flashMessage('success', 'Chicken Set Successfully!');
        return $this->redirect($this->generateUrl('chicken_type_set_list'));
    }

    /**
     * @Route("/chicken/type/create", name="chicken_type_create")
     * @Template("RbsSalesBundle:DamageGood:new.html.twig")
     * @param Request $request
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function agentBankInfoCreateAction(Request $request)
    {
        $chickenSet = new ChickenSetForAgent();
        $form = $this->createForm(new ChickenTypeSetForm($this->getUser()), $chickenSet, array(
            'action' => $this->generateUrl('chicken_type_create'), 'method' => 'POST',
            'attr' => array('novalidate' => 'novalidate')
        ));

        if ('POST' === $request->getMethod()) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $itemAgentCheck = $this->getDoctrine()->getManager()->getRepository('RbsSalesBundle:ChickenSetForAgent')->findOneBy(array(
                    'item' => $request->request->get('chicken_set_for_agent')['item'],
                    'agent'=> $request->request->get('chicken_set_for_agent')['agent']
                ));
                if($itemAgentCheck == null){
                    $this->getDoctrine()->getManager()->getRepository('RbsSalesBundle:ChickenSetForAgent')->create($chickenSet);
                }else{
                    $this->flashMessage('success', 'This Item Is Already Added!');
                    return $this->redirect($this->generateUrl('chicken_type_set_list'));
                }
                $this->flashMessage('success', 'Chicken Set Successfully!');
                return $this->redirect($this->generateUrl('chicken_type_set_list'));
            }
        }

        return array(
            'form' => $form->createView()
        );
    }
}