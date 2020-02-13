<?php

namespace Rbs\Bundle\SalesBundle\Controller;

use Doctrine\ORM\QueryBuilder;
use Rbs\Bundle\CoreBundle\Entity\Depo;
use Rbs\Bundle\CoreBundle\Entity\ItemType;
use Rbs\Bundle\CoreBundle\Repository\DepoRepository;
use Rbs\Bundle\CoreBundle\Repository\ItemRepository;
use Rbs\Bundle\SalesBundle\Entity\DailyDepotStock;
use Rbs\Bundle\SalesBundle\Entity\DailyDepotStockTransferred;
use Rbs\Bundle\SalesBundle\Form\Type\DepotStockTransferForm;
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


        if($stockItemOnHand >=($stock->getOnHold() + $stock->getTotalTransferredQuantity())){
            $stock->setOnHand($stockItemOnHand);
        }

        $em->persist($stock);
        $em->flush();

        $response = array(
            'onHand'     => $stock->getOnHand(),
            'onHold'     => $stock->getOnHold(),
            'onRemaining'     => $stock->getRemainingQuantity(),
        );

        return new JsonResponse($response);
    }



    /**
     * @Route("/depot/to/depot/stock/transfer", name="depot_to_depot_stock_transfer")
     * @Template("RbsSalesBundle:DailyDepotStock:transfer-stock-depot-to-depot.html.twig")
     * @param Request $request
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse|Response
     * @JMS\Secure(roles="ROLE_STOCK_VIEW, ROLE_STOCK_CREATE")
     */
    public function depotToDepotTransferAction(Request $request)
    {
        set_time_limit(0);
        $em = $this->getDoctrine()->getManager();

        $form = $this->createFormBuilder();
        $form->add('transferredFromDepot', 'entity', array(
            'class' => 'RbsCoreBundle:Depo',
            'required' => true,
            'empty_value' => 'Select Hatchery',
            'property' => 'name',
            'query_builder' => function (DepoRepository $repository)
            {
                return $repository->createQueryBuilder('d')
                    ->where('d.deletedAt IS NULL')
                    ->andWhere("d.depotType = :depotType")
                    ->setParameter('depotType', Depo::DEPOT_TYPE_CHICK)
                    ->orderBy('d.name','ASC');

            }
        ))
            ->add('transferredToDepot', 'entity', array(
            'class' => 'RbsCoreBundle:Depo',
            'required' => true,
            'empty_value' => 'Select Hatchery',
            'property' => 'name',
            'query_builder' => function (DepoRepository $repository)
            {
                return $repository->createQueryBuilder('d')
                    ->where('d.deletedAt IS NULL')
                    ->andWhere("d.depotType = :depotType")
                    ->setParameter('depotType', Depo::DEPOT_TYPE_CHICK)
                    ->orderBy('d.name','ASC');

            }
            ))

            ->add('item', 'entity', array(
                'class' => 'RbsCoreBundle:Item',
                'property' => 'name',
                'required' => true,
                'empty_value' => 'Select Item',
                'empty_data' => null,
                'query_builder' => function (ItemRepository $repository)
                {
                    return $repository->createQueryBuilder('i')
                        ->join('i.itemType','it')
                        ->where('i.deletedAt IS NULL')
                        ->andWhere('it.itemType =:type')
                        ->setParameter('type', ItemType::Chick)
                        ->orderBy('i.name','ASC');
                }
            ))
            ->add('transferredQuantity','integer',array(
                'required'   => true,
                'attr'=>array('min'=>1)
            ))
            ->add('submit', 'submit', array(
                'attr'     => array('class' => 'btn green')
            ))
        ;
        $form = $form->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            $data = $form->getData();
            $date = date('Y-m-d');
            $transferredFromStockId = $this->checkExistDailyStock($data['transferredFromDepot'], $data['item'], $date);
            $transferredToStockId = $this->checkExistDailyStock($data['transferredToDepot'], $data['item'], $date);

            if($transferredFromStockId && $transferredToStockId){

                if($transferredFromStockId[0]['id']!=$transferredToStockId[0]['id']){

                    $transferredDailyStockObj = $this->getDoctrine()->getRepository('RbsSalesBundle:DailyDepotStock')->find($transferredFromStockId[0]['id']);
                    $receivedDailyStockObj = $this->getDoctrine()->getRepository('RbsSalesBundle:DailyDepotStock')->find($transferredToStockId[0]['id']);

                    $dailyStockTransferred = new DailyDepotStockTransferred();
                    $dailyStockReceived = new DailyDepotStockTransferred();

                    if($transferredDailyStockObj && $receivedDailyStockObj && $transferredDailyStockObj->getRemainingQuantity()>=$data['transferredQuantity'] ){

                        $dailyStockTransferred->setTransferredQuantity($data['transferredQuantity']);
                        $dailyStockTransferred->setTransferredToDepot($data['transferredToDepot']);
                        $dailyStockTransferred->setDailyDepotStock($transferredDailyStockObj);

                        $this->getDoctrine()->getRepository('RbsSalesBundle:DailyDepotStockTransferred')->create($dailyStockTransferred);

                        $dailyStockReceived->setReceivedQuantity($data['transferredQuantity']);
                        $dailyStockReceived->setTransferredFromDepot($data['transferredFromDepot']);
                        $dailyStockReceived->setDailyDepotStock($receivedDailyStockObj);

                        $this->getDoctrine()->getRepository('RbsSalesBundle:DailyDepotStockTransferred')->create($dailyStockReceived);
                        $this->addFlash('success','Stock has been successfully transferred.');
                    }else{
                        $this->addFlash('error','Remaining quantity are not available');
                    }

                }else{
                    $this->addFlash('error','Stock transfer is not allowed in the same hatchery');
                }
            }else{
                $this->addFlash('error','Stock is not generated on this date');
            }

        }

        return array(
            'form' => $form->createView(),
        );
    }





}