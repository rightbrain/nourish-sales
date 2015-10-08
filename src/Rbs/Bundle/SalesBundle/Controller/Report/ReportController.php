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
     * @Route("/reports", name="reports_home")
     * @Template()
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @JMS\Secure(roles="ROLE_REPORT, ROLE_ADMIN")
     */
    public function indexAction()
    {
        return $this->render('RbsSalesBundle:Report:index.html.twig', array(

        ));
    }

    /**
     * @Route("/report/item", name="report_item")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @JMS\Secure(roles="ROLE_REPORT, ROLE_ADMIN")
     */
    public function orderItemReportAction(Request $request)
    {
        $form = new SearchType();
        $data = $request->get($form->getName());
        $form = $this->createForm($form);
        $form->submit($data);

        $items = $this->getDoctrine()->getRepository('RbsCoreBundle:Item')->allItems();
        $orderItems = $this->getDoctrine()->getRepository('RbsSalesBundle:OrderItem')->orderItemReport($_POST);

        foreach($items as $id => $row) {
            if(isset($orderItems[$id])) {
                $orderItemsReport[$id] = $orderItems[$id];
            }else{
                $orderItemsReport[$id]['id'] = $row['id'];
                $orderItemsReport[$id]['itemName'] = $row['name'];
                $orderItemsReport[$id]['quantity'] = 0;
                $orderItemsReport[$id]['totalAmount'] = 0;
            }
        }

        return $this->render('RbsSalesBundle:Report:itemReport.html.twig', array(
            'orderItems' => $orderItemsReport,
            'form' => $form->createView()
        ));
    }
}