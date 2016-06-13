<?php

namespace Rbs\Bundle\SalesBundle\Controller;

use Doctrine\ORM\QueryBuilder;
use Rbs\Bundle\SalesBundle\Entity\DamageGood;
use Rbs\Bundle\SalesBundle\Form\Type\DamageGoodForm;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use JMS\SecurityExtraBundle\Annotation as JMS;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * DamageGood Controller.
 *
 */
class DamageGoodController extends BaseController
{
    /**
     * @Route("/damage/good/list", name="damage_good_list")
     * @Method("GET")
     * @Template()
     */
    public function indexAction()
    {
        $datatable = $this->get('rbs_erp.sales.datatable.damage.good');
        $datatable->buildDatatable();

        return $this->render('RbsSalesBundle:DamageGood:index.html.twig', array(
            'datatable' => $datatable
        ));
    }

    /**
     * Lists all AgentsBankInfo entities.
     *
     * @Route("/damage_good_list_ajax", name="damage_good_list_ajax", options={"expose"=true})
     * @Method("GET")
     */
    public function listAjaxAction()
    {
        $datatable = $this->get('rbs_erp.sales.datatable.damage.good');
        $datatable->buildDatatable();

        $query = $this->get('sg_datatables.query')->getQueryFrom($datatable);
        /** @var QueryBuilder $qb */
        $function = function($qb)
        {
            $qb->join('damage_goods.agent', 'u');
            $qb->andWhere('u =:user');
            $qb->setParameter('user', $this->getUser());
        };
        $query->addWhereAll($function);
        
        return $query->getResponse();
    }

    /**
     * @Route("/damage/good/form", name="damage_good_form", options={"expose"=true})
     * @Template("RbsSalesBundle:DamageGood:form.html.twig")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function agentBankInfoCreateAction(Request $request)
    {
        $damageGood = new DamageGood();
        $form = $this->createForm(new DamageGoodForm($this->getUser()), $damageGood, array(
            'action' => $this->generateUrl('damage_good_form'), 'method' => 'POST',
            'attr' => array('novalidate' => 'novalidate')
        ));

        if ('POST' === $request->getMethod()) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $damageGood->setAgent($this->getUser());
                $this->getDoctrine()->getManager()->getRepository('RbsSalesBundle:DamageGood')->create($damageGood);
                $this->flashMessage('success', 'Damage Goods add Successfully!');
                return $this->redirect($this->generateUrl('damage_good_list'));
            }
        }

        return array(
            'form' => $form->createView()
        );
    }
}