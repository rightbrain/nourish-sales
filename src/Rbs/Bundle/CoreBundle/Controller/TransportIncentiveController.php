<?php

namespace Rbs\Bundle\CoreBundle\Controller;

use Doctrine\ORM\QueryBuilder;
use Rbs\Bundle\CoreBundle\Entity\SaleIncentive;
use Rbs\Bundle\CoreBundle\Entity\TransportIncentive;
use Rbs\Bundle\CoreBundle\Form\Type\SaleIncentiveForm;
use Rbs\Bundle\CoreBundle\Form\Type\TransportIncentiveForm;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use JMS\SecurityExtraBundle\Annotation as JMS;

/**
 * TransportIncentive Controller.
 *
 */
class TransportIncentiveController extends BaseController
{
    /**
     * @Route("/transport/incentive/list", name="transport_incentive_list")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listAction()
    {
        $transportIncentivesArr = array();
        $transportIncentives = $this->getDoctrine()->getRepository('RbsCoreBundle:TransportIncentive')->getAllTransportIncentive();
        $depos = $this->getDoctrine()->getRepository('RbsCoreBundle:Depo')->getAllActiveDepo();
        $itemTypes = $this->getDoctrine()->getRepository('RbsCoreBundle:ItemType')->getAllActiveItemType();
        $itemTypeCount = $this->getDoctrine()->getRepository('RbsCoreBundle:ItemType')->getItemTypeCount();

        foreach ($transportIncentives as $key => $transportIncentive) {
            $transportIncentivesArr[$transportIncentive['district']][$transportIncentive['station']]['data'][$transportIncentive['depo']][$transportIncentive['itemType']] = $transportIncentive['amount'];
            foreach ($depos as $depo) {
                foreach ($itemTypes as $itemType) {
                    if (!isset($transportIncentivesArr[$transportIncentive['district']][$transportIncentive['station']]['data'][$depo['name']][$itemType['itemType']])) {
                        $transportIncentivesArr[$transportIncentive['district']][$transportIncentive['station']]['data'][$depo['name']][$itemType['itemType']] = "0.0";
                    }
                }
            }
            ksort($transportIncentivesArr[$transportIncentive['district']][$transportIncentive['station']]['data'][$transportIncentive['depo']]);
        }

        $heads = array();
//        $head['district'] = 'District';
//        $head['station'] = 'Station';
        foreach ($depos as $key => $depo){
            $heads[$depo['name']] = $depo['name'];
            foreach ($itemTypes as $itemType){
                $heads[$key][$itemType['itemType']] = $itemType['itemType'];
            }
        }

//        print_r('<pre>');
//        print_r($head); exit;

        return $this->render('RbsCoreBundle:TransportIncentive:index.html.twig', array(
                'transportIncentivesArr' => $transportIncentivesArr,
                'depos' => $depos,
                'itemTypes' => $itemTypes,
                'heads' => $heads,
                'itemTypeCount' => $itemTypeCount
        ));
    }

    /**
     * @Route("/transport/incentive/create", name="transport_incentive_create")
     * @Template("RbsCoreBundle:TransportIncentive:form.html.twig")
     * @param Request $request
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function createAction(Request $request)
    {
        $transportIncentive = new TransportIncentive();

        $form = $this->createForm(new TransportIncentiveForm(), $transportIncentive, array(
            'action' => $this->generateUrl('transport_incentive_create'), 'method' => 'POST',
            'attr' => array('novalidate' => 'novalidate')
        ));

        if ('POST' === $request->getMethod()) {
            $form->handleRequest($request);
            if ($form->isValid()) {

                $transportIncentive->setDistrict($this->getDoctrine()->getRepository('RbsCoreBundle:Location')->find($request->request->get('transport_incentive')['level1']));
                $transportIncentive->setStation($this->getDoctrine()->getRepository('RbsCoreBundle:Location')->find($request->request->get('transport_incentive')['level2']));

                $this->getDoctrine()->getRepository('RbsCoreBundle:TransportIncentive')->create($transportIncentive);

                $this->flashMessage('success', 'Transport Incentive Add Successfully!');
                return $this->redirect($this->generateUrl('transport_incentive_list'));
            }
        }

        return array(
            'form' => $form->createView()
        );
    }
}