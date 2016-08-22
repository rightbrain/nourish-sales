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
     * @Route("/items/price/log/{item}", name="ItemPriceLogs_home", options={"expose"=true})
     * @Method("GET")
     * @Template()
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @JMS\Secure(roles="ROLE_ITEM_MANAGE")
     */
    public function indexAction(Request $request)
    {
        $itemId = $request->attributes->get('item');
        $itemHistories = $this->getDoctrine()->getRepository('RbsCoreBundle:ItemPriceLog')->findBy(array(
            'item' => $itemId
        ));

        $datatable = $this->get('rbs_erp.core.datatable.ItemPriceLog');
        $datatable->buildDatatable();

        return $this->render('RbsCoreBundle:ItemPriceLog:index.html.twig', array(
            'itemHistories' => $itemHistories,
            'datatable' => $datatable
        ));
    }

    /**
     * Lists all ItemPriceLog entities.
     *
     * @Route("/items_price_log_list_ajax/{item}", name="items_price_log_list_ajax", options={"expose"=true})
     * @Method("GET")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @JMS\Secure(roles="ROLE_ITEM_MANAGE")
     */
    public function listAjaxAction(Request $request)
    {
        $itemId = $request->attributes->get('item');
        $datatable = $this->get('rbs_erp.core.datatable.ItemPriceLog');
        $datatable->buildDatatable();

        $query = $this->get('sg_datatables.query')->getQueryFrom($datatable);
        /** @var QueryBuilder $qb */
        $function = function($qb) use ($itemId)
        {
            $qb->andWhere("core_items_price_log.item = :item");
            $qb->setParameter("item", $itemId);
        };

        $query->addWhereAll($function);
        return $query->getResponse();
    }
}
