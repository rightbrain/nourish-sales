<?php

namespace Rbs\Bundle\SalesBundle\Controller;

use Doctrine\ORM\QueryBuilder;
use Rbs\Bundle\SalesBundle\Form\Type\SwappingRsmForm;
use Rbs\Bundle\SalesBundle\Form\Type\SwappingSrForm;
use Rbs\Bundle\UserBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use JMS\SecurityExtraBundle\Annotation as JMS;

/**
 * Swapping Controller.
 *
 */
class SwappingController extends Controller
{
    /**
     * @Route("/swapping/rsm/list", name="swapping_rsm_list", options={"expose"=true})
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function swappingRsmListAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $rsmList = $em->getRepository('RbsUserBundle:User')->getRsmList();

        return $this->render('RbsSalesBundle:Swapping:rsm_list.html.twig', array(
            'rsmList' => $rsmList
        ));
    }

    /**
     * @Route("/swapping/rsm/create/{id}", name="swapping_rsm_create")
     * @Template("RbsSalesBundle:Swapping:swapping_rsm_form.html.twig")
     * @param Request $request
     * @param User $user
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function swappingRsmCreateAction(Request $request, User $user)
    {
        $em = $this->getDoctrine()->getManager();
        $form = $this->createForm(new SwappingRsmForm($user), $user);

        if ('POST' === $request->getMethod()) {
            $user->setArea($em->getRepository('RbsCoreBundle:Area')->find($request->request->get('rsm_swap')['areaNew']));

            $em->getRepository('RbsUserBundle:User')->update($user);
            $this->setSwappingData($request);
            $this->get('session')->getFlashBag()->add(
                    'success',
                    'Swap Successfully!'
                );

            return $this->redirect($this->generateUrl('swapping_rsm_list'));
        }

        return array(
            'form' => $form->createView()
        );
    }

    private function setSwappingData($request)
    {
        $em = $this->getDoctrine()->getManager();
        $userSwapping = $em->getRepository('RbsUserBundle:User')->find($request->request->get('rsm_swap')['userChange']);
        $userSwapping->setArea($em->getRepository('RbsCoreBundle:Area')->find($request->request->get('rsm_swap')['areaOld']));
        $this->getDoctrine()->getRepository('RbsUserBundle:User')->update($userSwapping);
    }

    /**
     * @Route("/swapping/sr/list", name="swapping_sr_list", options={"expose"=true})
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function swappingSrListAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $srList = $em->getRepository('RbsUserBundle:User')->getSrList();
        
        return $this->render('RbsSalesBundle:Swapping:sr_list.html.twig', array(
            'srList' => $srList
        ));
    }

    /**
     * @Route("/swapping/sr/create/{id}", name="swapping_sr_create")
     * @Template("RbsSalesBundle:Swapping:swapping_sr_form.html.twig")
     * @param Request $request
     * @param User $user
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function swappingSrCreateAction(Request $request, User $user)
    {
        $form = $this->createForm(new SwappingSrForm(), $user);

        if ('POST' === $request->getMethod()) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $this->getDoctrine()->getRepository('RbsUserBundle:User')->update($user);
                $this->get('session')->getFlashBag()->add(
                    'success',
                    'Swap Successfully!'
                );

                return $this->redirect($this->generateUrl('swapping_sr_list'));
            }
        }

        return array(
            'form' => $form->createView()
        );
    }
}