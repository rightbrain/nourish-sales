<?php

namespace Rbs\Bundle\SalesBundle\Controller;

use Doctrine\ORM\QueryBuilder;
use Rbs\Bundle\SalesBundle\Entity\Agent;
use Rbs\Bundle\SalesBundle\Entity\Delivery;
use Rbs\Bundle\SalesBundle\Entity\DeliveryPoint;
use Rbs\Bundle\SalesBundle\Entity\Vehicle;
use Rbs\Bundle\SalesBundle\Form\Type\VehicleDeliverySetForm;
use Rbs\Bundle\SalesBundle\Form\Type\VehicleForm;
use Rbs\Bundle\SalesBundle\Form\Type\DeliveryPointForm;
use Rbs\Bundle\UserBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use JMS\SecurityExtraBundle\Annotation as JMS;

/**
 * Delivery Point Controller.
 *
 */
class DeliveryPointController extends BaseController
{
    /**
     * @Route("/delivery-point/list", name="delivery_point_list")
     * @Method("GET")
     * @JMS\Secure(roles="ROLE_ADMIN")
     */
    public function indexAction()
    {
        $datatable = $this->get('rbs_erp.sales.datatable.DeliveryPoint');
        $datatable->buildDatatable();

        return $this->render('RbsSalesBundle:DeliveryPoint:index.html.twig', array(
            'datatable' => $datatable
        ));
    }

    /**
     * @Route("/delivery_point_list_ajax", name="delivery_point_list_ajax", options={"expose"=true})
     * @Method("GET")
     * @JMS\Secure(roles="ROLE_ADMIN")
     */
    public function listAjaxAction()
    {
        $user = $this->getUser();
        $datatable = $this->get('rbs_erp.sales.datatable.DeliveryPoint');
        $datatable->buildDatatable();

        $query = $this->get('sg_datatables.query')->getQueryFrom($datatable);
        /** @var QueryBuilder $qb */
        $function = function($qb)
        {
            $qb->andWhere("sales_delivery_points.deletedAt IS NULL");
        };
        $query->addWhereAll($function);

        return $query->getResponse();
    }

    /**
     * @Route("/delivery-point/add", name="delivery_point_add")
     * @Template("RbsSalesBundle:DeliveryPoint:form.html.twig")
     * @param Request $request
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse|Response
     * @JMS\Secure(roles="ROLE_ADMIN")
     */
    public function addAction(Request $request)
    {
        $deliveryPoint = new DeliveryPoint();
        $form = $this->createForm(new DeliveryPointForm(), $deliveryPoint);

        if ('POST' === $request->getMethod()) {
            $form->handleRequest($request);


            if ($form->isValid()) {
                $this->deliveryPointRepo()->create($deliveryPoint);
                $this->get('session')->getFlashBag()->add('success', 'Vehicle Info Added Successfully');
                return $this->redirect($this->generateUrl('delivery_point_list'));
            }
        }

        return array(
            'form' => $form->createView(),
        );
    }

    /**
     * @Route("/delivery-point/edit/{id}", name="delivery_point_edit", options={"expose"=true})
     * @Template("RbsSalesBundle:DeliveryPoint:form.html.twig")
     * @param Request $request
     * @param DeliveryPoint $deliveryPoint
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse|Response
     * @JMS\Secure(roles="ROLE_ADMIN")
     */
    public function editAction(Request $request, DeliveryPoint $deliveryPoint)
    {
        $form = $this->createForm(new DeliveryPointForm(), $deliveryPoint);

        if ('POST' === $request->getMethod()) {
            $form->handleRequest($request);


            if ($form->isValid()) {
                $this->deliveryPointRepo()->update($deliveryPoint);

                $this->get('session')->getFlashBag()->add('success', 'Delivery Point Updated Successfully');
                return $this->redirect($this->generateUrl('delivery_point_list'));
            }
        }

        return array(
            'form' => $form->createView(),
        );
    }

    /**
     * @return \Doctrine\Common\Persistence\ObjectManager|object
     */
    protected function em()
    {
        return $this->getDoctrine()->getManager();
    }

    /**
     * @return \Rbs\Bundle\SalesBundle\Repository\DeliveryPointRepository
     */
    protected function deliveryPointRepo()
    {
        return $this->em()->getRepository('RbsSalesBundle:DeliveryPoint');
    }

}