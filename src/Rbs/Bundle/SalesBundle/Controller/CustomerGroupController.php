<?php

namespace Rbs\Bundle\SalesBundle\Controller;

use Doctrine\ORM\QueryBuilder;
use Rbs\Bundle\SalesBundle\Entity\CustomerGroup;
use Rbs\Bundle\SalesBundle\Form\Type\CustomerGroupForm;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * Customer Group Controller.
 *
 */
class CustomerGroupController extends Controller
{
    /**
     * @Route("/customer-groups", name="customer_groups_home")
     * @Method("GET")
     * @Template()
     */
    public function indexAction()
    {
        $customerGroups = $this->getDoctrine()->getRepository('RbsSalesBundle:CustomerGroup')->customerGroups();
        $datatable = $this->get('rbs_erp.sales.datatable.customer.group');
        $datatable->buildDatatable();

        return $this->render('RbsSalesBundle:CustomerGroup:index.html.twig', array(
            'customerGroups' => $customerGroups,
            'datatable' => $datatable
        ));
    }

    /**
     * Lists all CustomerGroup entities.
     *
     * @Route("/customer_groups_list_ajax", name="customer_groups_list_ajax", options={"expose"=true})
     * @Method("GET")
     */
    public function listAjaxAction()
    {
        $datatable = $this->get('rbs_erp.sales.datatable.customer.group');
        $datatable->buildDatatable();

        $query = $this->get('sg_datatables.query')->getQueryFrom($datatable);
        /** @var QueryBuilder $qb */
        $function = function($qb)
        {
            $qb->andWhere("customer_groups.deletedAt IS NULL");
        };
        $query->addWhereAll($function);
        return $query->getResponse();
    }

    /**
     * @Route("/customer-group-create", name="customer_group_create")
     * @Template("RbsSalesBundle:CustomerGroup:new.html.twig")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function createAction(Request $request)
    {
        $customerGroup = new CustomerGroup();

        $form = $this->createForm(new CustomerGroupForm(), $customerGroup);

        if ('POST' === $request->getMethod()) {
            $form->handleRequest($request);

            if ($form->isValid()) {

                $this->getDoctrine()->getRepository('RbsSalesBundle:CustomerGroup')->create($customerGroup);

                $this->get('session')->getFlashBag()->add(
                    'success',
                    'Customer Group Create Successfully!'
                );

                return $this->redirect($this->generateUrl('customer_groups_home'));
            }
        }

        return array(
            'form' => $form->createView()
        );
    }

    /**
     * @Route("/customer-group-update/{id}", name="customer_group_update", options={"expose"=true})
     * @Template("RbsSalesBundle:CustomerGroup:new.html.twig")
     * @param Request $request
     * @param CustomerGroup $customerGroup
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function updateAction(Request $request, CustomerGroup $customerGroup)
    {
        $form = $this->createForm(new CustomerGroupForm(), $customerGroup);

        if ('POST' === $request->getMethod()) {
            $form->handleRequest($request);

            if ($form->isValid()) {

                $this->getDoctrine()->getRepository('RbsSalesBundle:CustomerGroup')->update($customerGroup);

                $this->get('session')->getFlashBag()->add(
                    'success',
                    'User Updated Successfully!'
                );

                return $this->redirect($this->generateUrl('customer_groups_home'));
            }
        }

        return array(
            'form' => $form->createView()
        );
    }

    /**
     * @Route("/customer-group-delete/{id}", name="customer_group_delete", options={"expose"=true})
     * @param CustomerGroup $customerGroup
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteAction(CustomerGroup $customerGroup)
    {
        $this->getDoctrine()->getManager()->remove($customerGroup);
        $this->getDoctrine()->getManager()->flush();

        $this->get('session')->getFlashBag()->add(
            'success',
            'Customer Group Successfully Delete'
        );

        return $this->redirect($this->generateUrl('customer_groups_home'));
    }
}