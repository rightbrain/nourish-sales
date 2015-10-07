<?php

namespace Rbs\Bundle\SalesBundle\Controller\Report;

use Doctrine\ORM\QueryBuilder;
use Rbs\Bundle\SalesBundle\Form\Search\Type\SearchType;
use Rbs\Bundle\SalesBundle\Form\Type\StockHistoryForm;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use JMS\SecurityExtraBundle\Annotation as JMS;

/**
 * Report Controller.
 *
 */
class ReportController extends Controller
{
    /**
     * @Route("/item-report", name="item_report")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @JMS\Secure(roles="ROLE_ADMIN")
     */
    public function orderItemReportAction(Request $request)
    {
        $form = new SearchType();
        $data = $request->get($form->getName());
        $form = $this->createForm($form);
        $form->submit($data);

        $orderItems = $this->getDoctrine()->getRepository('RbsSalesBundle:OrderItem')->orderItemReport($_POST);

        return $this->render('RbsSalesBundle:Report:itemReport.html.twig', array(
            'orderItems' => $orderItems,
            'form' => $form->createView()
        ));
    }
}