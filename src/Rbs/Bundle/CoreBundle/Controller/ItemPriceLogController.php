<?php

namespace Rbs\Bundle\CoreBundle\Controller;

use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Rbs\Bundle\CoreBundle\Entity\Item;
use Symfony\Component\HttpFoundation\Response;
use JMS\SecurityExtraBundle\Annotation as JMS;

/**
 * ItemPriceLog Controller.
 *
 */
class ItemPriceLogController extends BaseController
{
    /**
     * @Route("/items/price/log", name="ItemPriceLogs_home")
     * @Method("GET")
     * @Template()
     * @JMS\Secure(roles="ROLE_ITEM_MANAGE")
     */
    public function indexAction()
    {
        $datatable = $this->get('rbs_erp.core.datatable.ItemPriceLog');
        $datatable->buildDatatable();

        return $this->render('RbsCoreBundle:ItemPriceLog:index.html.twig', array(
            'datatable' => $datatable
        ));
    }

    /**
     * Lists all ItemPriceLog entities.
     *
     * @Route("/items_price_log_list_ajax", name="items_price_log_list_ajax", options={"expose"=true})
     * @Method("GET")
     * @JMS\Secure(roles="ROLE_ITEM_MANAGE")
     */
    public function listAjaxAction()
    {
        $datatable = $this->get('rbs_erp.core.datatable.ItemPriceLog');
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
