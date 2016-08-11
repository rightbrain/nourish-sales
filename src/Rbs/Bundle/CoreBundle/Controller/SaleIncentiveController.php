<?php

namespace Rbs\Bundle\CoreBundle\Controller;

use Rbs\Bundle\CoreBundle\Entity\SaleIncentive;
use Rbs\Bundle\CoreBundle\Form\Type\SaleIncentiveForm;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use JMS\SecurityExtraBundle\Annotation as JMS;

/**
 * SaleIncentive Controller.
 *
 */
class SaleIncentiveController extends BaseController
{
    /**
     * @Route("/sale/incentive/list", name="sale_incentive_list")
     * @return \Symfony\Component\HttpFoundation\Response
     * @JMS\Secure(roles="ROLE_SALE_INCENTIVE_MANAGE")
     */
    public function listAction()
    {
        $groupMonth = array();
        $groupYear = array();

        $saleIncentivesForMonthGroup = $this->getDoctrine()->getRepository('RbsCoreBundle:SaleIncentive')->getAllMonthIncentiveByMonthGroup();
        $totalMonthGroupName = $this->getDoctrine()->getRepository('RbsCoreBundle:SaleIncentive')->getTotalMonthGroupName();
        foreach ($totalMonthGroupName as $key => $groupName) {
            foreach ($saleIncentivesForMonthGroup as $saleIncentive) {
                if($saleIncentive['group'] == $groupName['group']){
                    $groupMonth[$key][$saleIncentive['name']] = $saleIncentive['name'];
                }
            }
        }

        $saleIncentivesForMonthGroup = $this->getDoctrine()->getRepository('RbsCoreBundle:SaleIncentive')->getAllMonthGroupIncentive();
        $saleIncentiveA = array();
        foreach ($saleIncentivesForMonthGroup as $i => $saleIncentive) {
            $saleIncentiveA[$saleIncentive['group']][] = $saleIncentive;
        }

        $saleIncentivesForYearGroup = $this->getDoctrine()->getRepository('RbsCoreBundle:SaleIncentive')->getAllYearGroupIncentive();
        $saleIncentiveB = array();
        foreach ($saleIncentivesForYearGroup as $i => $saleIncentive) {
            $saleIncentiveB[$saleIncentive['group']][] = $saleIncentive;
        }

        $saleIncentivesForYearGroup = $this->getDoctrine()->getRepository('RbsCoreBundle:SaleIncentive')->getAllYearIncentiveByMonthGroup();
        $totalYearGroupName = $this->getDoctrine()->getRepository('RbsCoreBundle:SaleIncentive')->getTotalYearGroupName();
        foreach ($totalYearGroupName as $key => $groupName) {
            foreach ($saleIncentivesForYearGroup as $saleIncentive) {
                if($saleIncentive['group'] == $groupName['group']){
                    $groupYear[$key][$saleIncentive['name']] = $saleIncentive['name'];
                }
            }
        }

        return $this->render('RbsCoreBundle:SaleIncentive:index.html.twig', array(
            'groupMonth' => $groupMonth,
            'groupYear' => $groupYear,
            'saleIncentiveA' => $saleIncentiveA,
            'saleIncentiveB' => $saleIncentiveB,
        ));
    }

    /**
     * @Route("/sale/incentive/create", name="sale_incentive_create")
     * @Template("RbsCoreBundle:SaleIncentive:form.html.twig")
     * @param Request $request
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @JMS\Secure(roles="ROLE_SALE_INCENTIVE_MANAGE")
     */
    public function createAction(Request $request)
    {
        $saleIncentive = new SaleIncentive();

        $form = $this->createForm(new SaleIncentiveForm(), $saleIncentive, array(
            'action' => $this->generateUrl('sale_incentive_create'), 'method' => 'POST',
            'attr' => array('novalidate' => 'novalidate')
        ));

        if ('POST' === $request->getMethod()) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                foreach($request->request->get('sale_incentive')['category'] as $value){
                    $saleIncentive = new SaleIncentive();
                    $saleIncentive->setAmount($request->request->get('sale_incentive')['amount']);
                    $saleIncentive->setCategory($this->getDoctrine()->getRepository('RbsCoreBundle:Category')->find($value));
                    $saleIncentive->setQuantity($request->request->get('sale_incentive')['quantity']);
                    $saleIncentive->setDurationType($request->request->get('sale_incentive')['durationType']);
                    $saleIncentive->setGroup($request->request->get('sale_incentive')['group']);
                    $saleIncentive->setType(SaleIncentive::SALE);
                    $this->getDoctrine()->getRepository('RbsCoreBundle:SaleIncentive')->create($saleIncentive);
                }

                $this->flashMessage('success', 'Sale Incentive Add Successfully!');
                return $this->redirect($this->generateUrl('sale_incentive_list'));
            }
        }

        return array(
            'form' => $form->createView()
        );
    }
}