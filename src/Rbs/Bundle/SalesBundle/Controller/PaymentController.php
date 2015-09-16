<?php

namespace Rbs\Bundle\SalesBundle\Controller;

use Doctrine\ORM\QueryBuilder;
use Rbs\Bundle\SalesBundle\Entity\Payment;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Payment Controller.
 *
 */
class PaymentController extends Controller
{
    /**
     * @Route("/payments", name="payments_home")
     * @Template()
     */
    public function indexAction()
    {
        $datatable = $this->get('rbs_erp.sales.datatable.payment');
        $datatable->buildDatatable();

        return $this->render('RbsSalesBundle:Payment:index.html.twig', array(
            'datatable' => $datatable
        ));
    }

    /**
     * Lists all Payment entities.
     *
     * @Route("/payment_list_ajax", name="payment_list_ajax", options={"expose"=true})
     * @Method("GET")
     */
    public function listAjaxAction()
    {
        $datatable = $this->get('rbs_erp.sales.datatable.payment');
        $datatable->buildDatatable();

        $query = $this->get('sg_datatables.query')->getQueryFrom($datatable);
        /** @var QueryBuilder $qb */
        $function = function($qb)
        {

        };
        $query->addWhereAll($function);
        return $query->getResponse();
    }
}