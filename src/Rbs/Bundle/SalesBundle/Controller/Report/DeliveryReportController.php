<?php
/**
 * Created by PhpStorm.
 * User: shahidul
 * Date: 7/9/19
 * Time: 2:51 PM
 */

namespace Rbs\Bundle\SalesBundle\Controller\Report;
use Knp\Snappy\Pdf;
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
     * @JMS\Secure(roles="ROLE_SALES_REPORT")
     */
    public function deliveryReportAction(Request $request)
    {
        $form = new DeliveryReportType();
        $data = $request->query->get($form->getName());
        $pdf_create = $request->query->get('pdf_create');
        $formSearch = $this->createForm($form, $data);

        $formSearch->submit($data);
        $deliveries = $this->getDoctrine()->getRepository('RbsSalesBundle:DeliveryItem')->getDeliveredItemsByDepo($data);
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
            $this->downloadPdf($html,'dailyDeliveryReportPdf.pdf');
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