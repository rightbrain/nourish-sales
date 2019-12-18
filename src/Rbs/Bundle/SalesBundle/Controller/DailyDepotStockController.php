<?php

namespace Rbs\Bundle\SalesBundle\Controller;

use Doctrine\ORM\QueryBuilder;
use Rbs\Bundle\SalesBundle\Entity\DailyDepotStock;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use JMS\SecurityExtraBundle\Annotation as JMS;

/**
 * DailyDepotStock Controller.
 *
 */
class DailyDepotStockController extends Controller
{

    /**
     * @Route("/depot/stock/create", name="hatchery_stock_create")
     * @Template("RbsSalesBundle:DailyDepotStock:add-daily-depot-stock.html.twig")
     * @param Request $request
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse|Response
     * @JMS\Secure(roles="ROLE_STOCK_VIEW, ROLE_STOCK_CREATE")
     */
    public function createAction(Request $request)
    {
        set_time_limit(0);
        $em = $this->getDoctrine()->getManager();
        $date = $request->request->get('date') ? date('Y-m-d H:i:s', strtotime($request->request->get('date'))) : date('Y-m-d H:i:s', time());
//
        $dailyStock = array();

        $depots = $this->getDoctrine()->getRepository('RbsCoreBundle:Depo')->getAllActiveDepotForChick();
        $chickItems = $this->getDoctrine()->getRepository('RbsCoreBundle:Item')->getChickItems();
        if($request->request->get('date')){

            if($request->request->get('date') >= date('d-m-Y')){
                foreach ($depots as $depot){

                    foreach ($chickItems as $item){
                        $existingDailyStock = $this->checkExistDailyStock($depot, $item, $date);
                        if(!$existingDailyStock){
                            $stockHistory = new DailyDepotStock();
                            $stockHistory->setDepo($depot);
                            $stockHistory->setItem($item);
                            $stockHistory->setOnHand(0);
                            $stockHistory->setOnHold(0);
                            $stockHistory->setCreatedAt(new \DateTime($date));
                            $stockHistory->setUpdatedAt(new \DateTime($date));
                            $em->persist($stockHistory);
                        }
                    }
                }
                $em->flush();

                $dailyStock = $this->getDoctrine()->getRepository('RbsSalesBundle:DailyDepotStock')->getDailyStock($date);
            }else{
                $this->get('session')->getFlashBag()->add('error', 'Please enter valid date');
            }
        }

        return array(
            'date'=> $request->request->get('date'),
            'depots'=> $depots,
            'items'=> $chickItems,
            'dailyStock'=> $dailyStock,
        );
    }


    private function checkExistDailyStock($depot, $item, $date){
        $repo = $this->getDoctrine()->getRepository('RbsSalesBundle:DailyDepotStock');
        $qb = $repo->createQueryBuilder('dds');
        $qb->select('dds.id');
        $qb->where($qb->expr()->between('dds.createdAt', ':start', ':end'));
        $qb->setParameters(array('start' => $date . ' 00:00:00', 'end' => $date . ' 23:59:59'));
        $qb->andWhere('dds.depo = :depo')->setParameter('depo', $depot);
        $qb->andWhere('dds.item = :item')->setParameter('item', $item);

        return $qb->getQuery()->getResult();
    }

    /**
     * update stock item ajax
     * @Route("update_daily_depot_stock_ajax/{stock}", name="update_daily_depot_stock_ajax", options={"expose"=true})
     * @param Request $request
     * @return Response
     * @JMS\Secure(roles="ROLE_STOCK_VIEW, ROLE_STOCK_CREATE")
     */
    public function findItemPriceDepoAction(Request $request, DailyDepotStock $stock)
    {
        $stockItemOnHand = $request->request->get('stockItemOnHand');
        $em = $this->getDoctrine()->getManager();


        $stock->setOnHand($stockItemOnHand);

        $em->persist($stock);
        $em->flush();

        $response = array(
            'onHand'     => $stock->getOnHand(),
            'onHold'     => $stock->getOnHold(),
            'onRemaining'     => $stock->getOnHand()- $stock->getOnHold(),
        );

        return new JsonResponse($response);
    }



}