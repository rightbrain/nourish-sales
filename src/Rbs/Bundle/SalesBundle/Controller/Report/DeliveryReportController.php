<?php
/**
 * Created by PhpStorm.
 * User: shahidul
 * Date: 7/9/19
 * Time: 2:51 PM
 */

namespace Rbs\Bundle\SalesBundle\Controller\Report;

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
        $formSearch = $this->createForm($form, $data);
        if ($request->query->get('delivery_report[start_date]', null, true)) {
            $data['start_date'] = date('Y-m-d', strtotime($data['start_date']));
        }

        $formSearch->submit($data);
        $deliveries = $this->getDoctrine()->getRepository('RbsSalesBundle:DeliveryItem')->getDeliveredItemsByDepo($data);

        return $this->render('RbsSalesBundle:Report/Delivery:daily-delivery-report.html.twig', array(
            'formSearch' => $formSearch->createView(),
            'data' => $data,
            'deliveries' => $deliveries,
        ));
    }

}