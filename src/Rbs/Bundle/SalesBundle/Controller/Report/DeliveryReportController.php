<?php
/**
 * Created by PhpStorm.
 * User: shahidul
 * Date: 7/9/19
 * Time: 2:51 PM
 */

namespace Rbs\Bundle\SalesBundle\Controller\Report;
use Knp\Snappy\Pdf;
use Rbs\Bundle\SalesBundle\Form\Search\Type\DeliveryBreedWiseReportChickType;
use Rbs\Bundle\SalesBundle\Form\Search\Type\DeliveryReportChickType;
use Rbs\Bundle\SalesBundle\Form\Search\Type\DeliveryReportType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use JMS\SecurityExtraBundle\Annotation as JMS;


class DeliveryReportController extends Controller
{

    /**
     * @Route("/report/delivery", name="report_delivery")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @JMS\Secure(roles="ROLE_SUPER_ADMIN, ROLE_FEED_DELIVERY_REPORT")
     */
    public function deliveryReportAction(Request $request)
    {
        $form = new DeliveryReportType($this->getUser());
        $data = $request->query->get($form->getName());
        $pdf_create = $request->query->get('pdf_create');
        $formSearch = $this->createForm($form, $data);

        $formSearch->submit($data);
        $deliveries = $this->getDoctrine()->getRepository('RbsSalesBundle:DeliveryItem')->getDeliveredItemsByDepo($this->getUser(), $data);
        if(empty($pdf_create)){

            return $this->render('RbsSalesBundle:Report/Delivery:daily-delivery-report.html.twig', array(
                'formSearch' => $formSearch->createView(),
                'data' => $data,
                'deliveries' => $deliveries,
            ));

        }else{
            $html = $this->renderView('RbsSalesBundle:Report/Delivery:daily-delivery-report-pdf.html.twig', array(
                    'formSearch' => $formSearch->createView(),
                    'data' => $data,
                    'deliveries' => $deliveries,
                )
            );
            $this->downloadPdf($html,time().'_dailyDeliveryReportPdf.pdf');
        }

    }

    /**
     * @Route("/report/feed/delivery/item", name="report_feed_delivery_item")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @JMS\Secure(roles="ROLE_SUPER_ADMIN, ROLE_FEED_DELIVERY_REPORT")
     */
    public function feedDeliveryItemReportAction(Request $request)
    {
        $form = new DeliveryReportType($this->getUser());
        $data = $request->query->get($form->getName());
        $pdf_create = $request->query->get('pdf_create');
        $formSearch = $this->createForm($form, $data);

        $formSearch->submit($data);
        $deliveries = $this->getDoctrine()->getRepository('RbsSalesBundle:DeliveryItem')->getFeedDeliveredItemsByDepotAndDateRange($this->getUser(), $data);
        if(empty($pdf_create)){

            return $this->render('RbsSalesBundle:Report/DeliveryItem:delivery-item-report.html.twig', array(
                'formSearch' => $formSearch->createView(),
                'data' => $data,
                'deliveries' => $deliveries,
            ));

        }else{
            $html = $this->renderView('RbsSalesBundle:Report/DeliveryItem:delivery-item-report-pdf.html.twig', array(
                    'formSearch' => $formSearch->createView(),
                    'data' => $data,
                    'deliveries' => $deliveries,
                )
            );
            $this->downloadPdf($html,time().'_dailyDeliveryReportPdf.pdf');
        }

    }

    /**
     * @Route("/report/chick/delivery", name="report_chick_delivery")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @JMS\Secure(roles="ROLE_CHICK_DELIVERY_REPORT")
     */
    public function chickDeliveryReportAction(Request $request)
    {
        $form = new DeliveryReportChickType();
        $data = $request->query->get($form->getName());
        $pdf_create = $request->query->get('pdf_create');
        $formSearch = $this->createForm($form, $data);

        $formSearch->submit($data);
        $date = $data['start_date'] ? date('Y-m-d', strtotime($data['start_date'])) : date('Y-m-d', strtotime('now'));
        $depotId= $data['depo']?$data['depo']:'';
        $depot=null;
        if($depotId){
            $depot = $this->getDoctrine()->getRepository('RbsCoreBundle:Depo')->find($depotId);
        }
        $deliveries = $this->getDoctrine()->getRepository('RbsSalesBundle:DeliveryItem')->getChickDeliveredItemsByDepo($data);
        $dailyDepotStock = $this->getDoctrine()->getRepository('RbsSalesBundle:DailyDepotStock')->getDailyStockByDateDepot($date, $data['depo']);
        $chickItems = $this->getDoctrine()->getRepository('RbsCoreBundle:Item')->getChickItems();

        if(empty($pdf_create)){

            return $this->render('RbsSalesBundle:Report/Delivery:daily-chick-delivery-report.html.twig', array(
                'formSearch' => $formSearch->createView(),
                'data' => $data,
                'deliveries' => $deliveries,
                'dailyDepotStock' => $dailyDepotStock,
                'chickItems' => $chickItems,
                'depot' => $depot,
            ));

        }else{
            $html = $this->renderView('RbsSalesBundle:Report/Delivery:daily-chick-delivery-report-pdf.html.twig', array(
                    'formSearch' => $formSearch->createView(),
                    'data' => $data,
                    'deliveries' => $deliveries,
                    'dailyDepotStock' => $dailyDepotStock,
                    'chickItems' => $chickItems,
                    'depot' => $depot,
                )
            );
            $this->downloadPdf($html,'dailyDeliveryReportPdf_'.time().'.pdf');
        }

    }

    /**
     * @Route("/report/chick/breed/wise/delivery", name="report_chick_breed_wise_daily_delivery")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @JMS\Secure(roles="ROLE_CHICK_DELIVERY_REPORT")
     */
    public function chickBreedWiseDailyDeliveryReportAction(Request $request)
    {
        $form = new DeliveryBreedWiseReportChickType();
        $data = $request->query->get($form->getName());
        $pdf_create = $request->query->get('pdf_create');
        $formSearch = $this->createForm($form, $data);

        $formSearch->submit($data);
        $date = $data['start_date'] ? date('Y-m-d', strtotime($data['start_date'])) : date('Y-m-d', strtotime('now'));

        $deliveries = $this->getDoctrine()->getRepository('RbsSalesBundle:DeliveryItem')->getChickDeliveredItemsByDepotLocationBreed($data);
        $chickItems = $this->getDoctrine()->getRepository('RbsCoreBundle:Item')->getChickItems();

        if(empty($pdf_create)){

            return $this->render('RbsSalesBundle:Report/Delivery:daily-chick-delivery-breed-wise-report.html.twig', array(
                'formSearch' => $formSearch->createView(),
                'data' => $data,
                'deliveries' => $deliveries,
                'chickItems' => $chickItems,
            ));

        }else{
            $html = $this->renderView('RbsSalesBundle:Report/Delivery:daily-chick-delivery-breed-wise-report-pdf.html.twig', array(
                    'formSearch' => $formSearch->createView(),
                    'data' => $data,
                    'deliveries' => $deliveries,
                    'chickItems' => $chickItems,
                )
            );
            $this->downloadPdf($html,'dailyDeliveryReportPdf_'.time().'.pdf');
        }

    }

    /**
     * @Route("/report/chick/region/wise/delivery", name="report_chick_region_wise_daily_delivery")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @JMS\Secure(roles="ROLE_CHICK_DELIVERY_REPORT")
     */
    public function chickRegionWiseDailyDeliveryReportAction(Request $request)
    {
        $form = new DeliveryBreedWiseReportChickType();
        $data = $request->query->get($form->getName());
        $pdf_create = $request->query->get('pdf_create');
        $formSearch = $this->createForm($form, $data);

        $formSearch->submit($data);
        $date = $data['start_date'] ? date('Y-m-d', strtotime($data['start_date'])) : date('Y-m-d', strtotime('now'));

        $deliveries = $this->getDoctrine()->getRepository('RbsSalesBundle:DeliveryItem')->getChickDeliveredItemsByDepotLocationBreed($data);
        $chickItems = $this->getDoctrine()->getRepository('RbsCoreBundle:Item')->getChickItems();

        if(empty($pdf_create)){

            return $this->render('RbsSalesBundle:Report/Delivery:daily-chick-delivery-region-wise-report.html.twig', array(
                'formSearch' => $formSearch->createView(),
                'data' => $data,
                'deliveries' => $deliveries,
                'chickItems' => $chickItems,
            ));

        }else{
            $html = $this->renderView('RbsSalesBundle:Report/Delivery:daily-chick-delivery-region-wise-report-pdf.html.twig', array(
                    'formSearch' => $formSearch->createView(),
                    'data' => $data,
                    'deliveries' => $deliveries,
                    'chickItems' => $chickItems,
                )
            );
            $this->downloadPdf($html,'dailyDeliveryReportPdf_'.time().'.pdf');
        }

    }

    /**
     * @Route("/report/chick/district/wise/delivery", name="report_chick_district_wise_daily_delivery")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @JMS\Secure(roles="ROLE_CHICK_DELIVERY_REPORT")
     */
    public function chickDistrictWiseDailyDeliveryReportAction(Request $request)
    {
        $form = new DeliveryBreedWiseReportChickType();
        $data = $request->query->get($form->getName());
        $pdf_create = $request->query->get('pdf_create');
        $formSearch = $this->createForm($form, $data);

        $formSearch->submit($data);
        $date = $data['start_date'] ? date('Y-m-d', strtotime($data['start_date'])) : date('Y-m-d', strtotime('now'));

        $deliveries = $this->getDoctrine()->getRepository('RbsSalesBundle:DeliveryItem')->getChickDeliveredItemsByDepotLocationBreed($data);
        $chickItems = $this->getDoctrine()->getRepository('RbsCoreBundle:Item')->getChickItems();

        if(empty($pdf_create)){

            return $this->render('RbsSalesBundle:Report/Delivery:daily-chick-delivery-district-wise-report.html.twig', array(
                'formSearch' => $formSearch->createView(),
                'data' => $data,
                'deliveries' => $deliveries,
                'chickItems' => $chickItems,
            ));

        }else{
            $html = $this->renderView('RbsSalesBundle:Report/Delivery:daily-chick-delivery-district-wise-report-pdf.html.twig', array(
                    'formSearch' => $formSearch->createView(),
                    'data' => $data,
                    'deliveries' => $deliveries,
                    'chickItems' => $chickItems,
                )
            );
            $this->downloadPdf($html,'dailyDeliveryReportPdf_'.time().'.pdf');
        }

    }

    public function downloadPdf($html,$fileName = '')
    {
        $wkhtmltopdfPath = 'xvfb-run --server-args="-screen 0, 1280x1024x24" /usr/bin/wkhtmltopdf --use-xserver';
        $snappy          = new Pdf($wkhtmltopdfPath);
        $pdf             = $snappy->getOutputFromHtml($html);
        header('Content-Type: application/pdf');
        header("Content-Disposition: attachment; filename={$fileName}");
        echo $pdf;
        return new Response('');
    }

}