<?php

namespace Rbs\Bundle\CoreBundle\Controller;

use Doctrine\ORM\QueryBuilder;
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
     */
    public function listAction()
    {
        $saleIncentivesForMonth = $this->getDoctrine()->getRepository('RbsCoreBundle:SaleIncentive')->getAllMonthIncentiveByGroup();
        $saleIncentivesForYear = $this->getDoctrine()->getRepository('RbsCoreBundle:SaleIncentive')->getAllMonthIncentiveByYear();

        return $this->render('RbsCoreBundle:SaleIncentive:index.html.twig', array(
            'saleIncentivesForMonth' => $saleIncentivesForMonth,
            'saleIncentivesForYear' => $saleIncentivesForYear
        ));
    }

    /**
     * @Route("/sale/incentive/create", name="sale_incentive_create")
     * @Template("RbsCoreBundle:SaleIncentive:form.html.twig")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
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
                    $saleIncentive->setIncentiveGroup($request->request->get('sale_incentive')['incentiveGroup']);
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