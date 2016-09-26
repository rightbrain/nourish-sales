<?php

namespace Rbs\Bundle\CoreBundle\Controller;

use Rbs\Bundle\CoreBundle\Entity\TransportIncentive;
use Rbs\Bundle\CoreBundle\Form\Type\TransportIncentiveForm;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use JMS\SecurityExtraBundle\Annotation as JMS;
use Rbs\Bundle\CoreBundle\Entity\Upload;
use Rbs\Bundle\CoreBundle\Form\Type\UploadForm;

/**
 * TransportIncentive Controller.
 *
 */
class TransportIncentiveController extends BaseController
{
    /**
     * @Route("/transport/incentive/list", name="transport_incentive_list")
     * @return \Symfony\Component\HttpFoundation\Response
     * @JMS\Secure(roles="ROLE_TRANSPORT_INCENTIVE_MANAGE")
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

        return $this->render('RbsCoreBundle:TransportIncentive:index.html.twig', array(
                'transportIncentivesArr' => $transportIncentivesArr,
                'depos' => $depos,
                'itemTypes' => $itemTypes,
                'itemTypeCount' => $itemTypeCount
        ));
    }

    /**
     * @Route("/transport/incentive/create", name="transport_incentive_create")
     * @Template("RbsCoreBundle:TransportIncentive:form.html.twig")
     * @param Request $request
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @JMS\Secure(roles="ROLE_TRANSPORT_INCENTIVE_MANAGE")
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

                $preTIs = $this->getDoctrine()->getRepository('RbsCoreBundle:TransportIncentive')->getTransportIncentiveForStatusChange($request->request->get('transport_incentive')['level1'], $request->request->get('transport_incentive')['level2'],
                    $request->request->get('transport_incentive')['depo'], $request->request->get('transport_incentive')['itemType']);
                foreach ($preTIs as $preTI){
                    $preTI->setStatus(TransportIncentive::ARCHIVED);
                    $this->getDoctrine()->getRepository('RbsCoreBundle:TransportIncentive')->update($preTI);
                }
                
                $transportIncentive->setDistrict($this->getDoctrine()->getRepository('RbsCoreBundle:Location')->find($request->request->get('transport_incentive')['level1']));
                $transportIncentive->setStation($this->getDoctrine()->getRepository('RbsCoreBundle:Location')->find($request->request->get('transport_incentive')['level2']));
                $transportIncentive->setStatus(TransportIncentive::CURRENT);
                
                $this->getDoctrine()->getRepository('RbsCoreBundle:TransportIncentive')->create($transportIncentive);

                $this->flashMessage('success', 'Transport Incentive Add Successfully!');
                return $this->redirect($this->generateUrl('transport_incentive_list'));
            }
        }

        return array(
            'form' => $form->createView()
        );
    }

    /**
     * @Route("/transport/incentive/import", name="transport_incentive_import")
     * @Template("RbsCoreBundle:TransportIncentive:import-form.html.twig")
     * @param Request $request
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function importAction(Request $request)
    {
        $upload = new Upload();
        $form = $this->createForm(new UploadForm(), $upload);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $file = $upload->getFile();
            $fileName = md5(uniqid()).'.csv';
            $file->move(
                $this->getParameter('brochures_directory'),
                $fileName
            );
            $upload->setFile($fileName);

            $file = $this->get('request')->getSchemeAndHttpHost().'/uploads/sales/csv-import/'.$fileName;
            if (($handle = fopen($file, "r")) !== FALSE) {
                fgetcsv($handle);
                while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                    $num = count($data);
                    for ($c=0; $c < $num; $c++) {
                        $col[$c] = $data[$c];
                    }
                    $transportIncentiveOlds = $this->getDoctrine()->getRepository('RbsCoreBundle:TransportIncentive')->getTransportIncentiveForStatusChange($this->getLocationByName($col[0]), $this->getLocationByName($col[1]), $this->getDepoByName($col), $this->getItemTypeByName($col));
                    foreach ($transportIncentiveOlds as $transportIncentiveOld){
                        $transportIncentiveOld->setStatus(TransportIncentive::ARCHIVED);
                        $this->getDoctrine()->getRepository('RbsCoreBundle:TransportIncentive')->create($transportIncentiveOld);
                    }
                    $transportIncentive = new TransportIncentive();
                    $transportIncentive->setStatus(TransportIncentive::CURRENT);
                    $transportIncentive->setDistrict($this->getLocationByName($col[0]));
                    $transportIncentive->setStation($this->getLocationByName($col[1]));
                    $transportIncentive->setDepo($this->getDepoByName($col));
                    $transportIncentive->setItemType($this->getItemTypeByName($col));
                    $transportIncentive->setAmount($col[4]);
                    $this->getDoctrine()->getRepository('RbsCoreBundle:TransportIncentive')->create($transportIncentive);
                }
                fclose($handle);
            }

            $this->flashMessage('success', 'Transport Incentive Import Successfully!');
            return $this->redirect($this->generateUrl('transport_incentive_list'));
        }

        return array(
            'form' => $form->createView()
        );
    }

    /**
     * @param $col
     * @return mixed
     */
    protected function getLocationByName($col)
    {
        return $this->getDoctrine()->getRepository('RbsCoreBundle:Location')->findOneByName($col);
    }

    /**
     * @param $col
     * @return mixed
     */
    protected function getDepoByName($col)
    {
        return $this->getDoctrine()->getRepository('RbsCoreBundle:Depo')->findOneByName($col[2]);
    }

    /**
     * @param $col
     * @return mixed
     */
    protected function getItemTypeByName($col)
    {
        return $this->getDoctrine()->getRepository('RbsCoreBundle:ItemType')->findOneByItemType($col[3]);
    }
}