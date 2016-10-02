<?php

namespace Rbs\Bundle\SalesBundle\Controller;

use Rbs\Bundle\SalesBundle\Entity\ChickenSetForAgent;
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

        return $this->render('RbsSalesBundle:DamageGood:chicken-type.html.twig', array(
            "agents" => $agents
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

        return $this->redirect($this->generateUrl('chicken_type_set_list'));
    }
}