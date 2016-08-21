<?php

namespace Rbs\Bundle\SalesBundle\Controller;

use Doctrine\DBAL\Query\QueryBuilder;
use JMS\SecurityExtraBundle\Annotation as JMS;
use Rbs\Bundle\SalesBundle\Entity\Delivery;
use Rbs\Bundle\SalesBundle\Entity\Order;
use Rbs\Bundle\SalesBundle\Event\DeliveryEvent;
use Rbs\Bundle\SalesBundle\Event\OrderApproveEvent;
use Rbs\Bundle\SalesBundle\Form\Type\DeliveryForm;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * User Controller.
 *
 */
class DeliveryController extends BaseController
{
    /**
     * @Route("/deliveries", name="deliveries_home")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @JMS\Secure(roles="ROLE_DELIVERY_MANAGE")
     */
    public function indexAction(Request $request)
    {
        $datatable = $this->get('rbs_erp.sales.datatable.delivery');
        $datatable->buildDatatable();

        return $this->render('RbsSalesBundle:Delivery:index.html.twig', array(
            'datatable' => $datatable,
        ));
    }

    /**
     * @Route("/delivery_list_ajax", name="delivery_list_ajax", options={"expose"=true})
     * @Method("GET")
     * @JMS\Secure(roles="ROLE_DELIVERY_MANAGE")
     */
    public function listAjaxAction(Request $request)
    {
        $datatable = $this->get('rbs_erp.sales.datatable.delivery');
        $datatable->buildDatatable();

        $dateFilter = $request->query->get('columns[1][search][value]', null, true);

        $columns = $request->query->get('columns');
        $columns[1]['search']['value'] = '';
        $request->query->set('columns', $columns);

        $query = $this->get('sg_datatables.query')->getQueryFrom($datatable);

        /** @var QueryBuilder $qb */
        $function = function($qb) use ($dateFilter)
        {
            $qb->join('deliveries.depo', 'd');
            $qb->join('d.users', 'u');
            $qb->andWhere('u =:user');
            $qb->setParameter('user', $this->getUser());

            $qb->andWhere('orderRef.deliveryState IN (:deliveryState)')
                ->setParameter('deliveryState', array(Order::DELIVERY_STATE_READY));
            if ($dateFilter) {
                $qb->andWhere('orderRef.createdAt BETWEEN :fromDate AND :toDate')
                    ->setParameter('fromDate', date('Y-m-d 00:00:00', strtotime($dateFilter)))
                    ->setParameter('toDate', date('Y-m-d 23:59:59', strtotime($dateFilter)));
            }
        };
        $query->addWhereAll($function);

        return $query->getResponse();
    }

    /**
     * @Route("/delivery/{id}", name="delivery_view", options={"expose"=true})
     * @param Delivery $delivery
     * @return \Symfony\Component\HttpFoundation\Response
     * @JMS\Secure(roles="ROLE_DELIVERY_MANAGE")
     */
    public function view(Delivery $delivery)
    {
        $partialItems = $this->getDoctrine()->getRepository('RbsSalesBundle:DeliveryItem')->getPartialDeliveredItems($delivery);

        return $this->render('RbsSalesBundle:Delivery:view.html.twig', array(
            'delivery'  => $delivery,
            'order'     => $delivery->getOrderRef(),
            'agent'  => $delivery->getOrderRef()->getAgent(),
            'partialItems' => $partialItems
        ));
    }

    /**
     * @Route("/delivery/{id}/vehicle-in", name="delivery_vehicle_in")
     * @param Delivery $delivery
     * @return \Symfony\Component\HttpFoundation\Response
     * @JMS\Secure(roles="ROLE_DELIVERY_MANAGE")
     */
    public function vehicleInAction(Delivery $delivery)
    {
        if (!$delivery->getVehicleIn()) {
            $delivery->setVehicleIn(new \DateTime());
            $this->getDoctrine()->getManager()->persist($delivery);
            $this->getDoctrine()->getManager()->flush();

            $this->dispatch('delivery.vehicle_in', new DeliveryEvent($delivery));
        }

        return new JsonResponse();
    }

    /**
     * @Route("/delivery/{id}/start-loading", name="delivery_start_loading")
     * @param Delivery $delivery
     * @return \Symfony\Component\HttpFoundation\Response
     * @JMS\Secure(roles="ROLE_DELIVERY_MANAGE")
     */
    public function startLoadingAction(Delivery $delivery)
    {
        if (!$delivery->getStartLoad()) {
            $delivery->setStartLoad(new \DateTime());
            $this->getDoctrine()->getManager()->persist($delivery);
            $this->getDoctrine()->getManager()->flush();

            $this->dispatch('delivery.start_loading', new DeliveryEvent($delivery));
        }

        return new JsonResponse();
    }

    /**
     * @Route("/delivery/{id}/finish-loading", name="delivery_finish_loading")
     * @param Delivery $delivery
     * @return \Symfony\Component\HttpFoundation\Response
     * @JMS\Secure(roles="ROLE_DELIVERY_MANAGE")
     */
    public function finishLoadingAction(Delivery $delivery)
    {
        if (!$delivery->getFinishLoad()) {
            $delivery->setFinishLoad(new \DateTime());
            $this->getDoctrine()->getManager()->persist($delivery);
            $this->getDoctrine()->getManager()->flush();

            $this->dispatch('delivery.finish_loading', new DeliveryEvent($delivery));
        }

        return new JsonResponse();
    }

    /**
     * @Route("/delivery/{id}/vehicle-out", name="delivery_vehicle_out")
     * @param Delivery $delivery
     * @return \Symfony\Component\HttpFoundation\Response
     * @JMS\Secure(roles="ROLE_DELIVERY_MANAGE")
     */
    public function vehicleOutAction(Delivery $delivery)
    {
        if (!$delivery->getVehicleOut()) {
            $delivery->setVehicleOut(new \DateTime());
            $this->getDoctrine()->getManager()->persist($delivery);
            $this->getDoctrine()->getManager()->flush();

            $this->dispatch('delivery.vehicle_out', new DeliveryEvent($delivery));
        }

        return new JsonResponse();
    }

    /**
     * @Route("/delivery/{id}/save", name="delivery_save", options={"expose"=true})
     * @param Delivery $delivery
     * @return \Symfony\Component\HttpFoundation\Response
     * @JMS\Secure(roles="ROLE_DELIVERY_MANAGE")
     */
    public function saveAction(Delivery $delivery)
    {
        /** TODO: Service Side Stock Check */

        $data = $this->getDoctrine()->getRepository('RbsSalesBundle:Delivery')->save($delivery, $this->get('request')->request->all());

        $this->getDoctrine()->getRepository('RbsSalesBundle:Order')->updateDeliveryState($data['orders']);
        $this->getDoctrine()->getRepository('RbsSalesBundle:Stock')->removeStockFromOnHold($delivery);

        $this->dispatch('delivery.delivered', new DeliveryEvent($delivery));

        $this->flashMessage('success', 'Order #' . $delivery->getOrderRef()->getId() . ' ' . $delivery->getOrderRef()->getDeliveryState() . ' Successfully');

        return new Response();
    }

    /**
     * @Route("/update-delivery/{id}", name="update_delivery", options={"expose"=true})
     * @Template("RbsSalesBundle:Delivery:_edit.html.twig")
     * @param Request $request
     * @param Delivery $delivery
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     * @JMS\Secure(roles="ROLE_ORDER_EDIT")
     */
    public function updateDeliveryAction(Request $request, Delivery $delivery)
    {
        $form = $this->createForm(
            new DeliveryForm(), $delivery, array(
                'action' => $this->generateUrl('update_delivery', array('id' => $delivery->getId())),
                'attr'   => array(
                    'novalidate' => 'novalidate',
                ),
            )
        );

        if ('POST' === $request->getMethod()) {

            $form->handleRequest($request);

            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();

                $em->persist($delivery);
                $em->flush();

                $this->flashMessage('success', 'Delivery Information Update Successfully!');

                return $this->redirect($this->generateUrl('order_details', array('id' => $delivery->getOrderRef()->getId())));
            }
        }

        return array(
            'form' => $form->createView(),
        );
    }
}