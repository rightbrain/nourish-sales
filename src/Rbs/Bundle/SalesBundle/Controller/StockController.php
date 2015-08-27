<?php

namespace Rbs\Bundle\SalesBundle\Controller;

use Doctrine\ORM\QueryBuilder;
use Rbs\Bundle\SalesBundle\Entity\Stock;
use Rbs\Bundle\SalesBundle\Form\Type\StockForm;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * User Controller.
 *
 */
class StockController extends Controller
{
    /**
     * @Route("/stocks", name="stocks_home")
     * @Method("GET")
     * @Template()
     */
    public function indexAction()
    {
        $datatable = $this->get('rbs_erp.sales.datatable.stock');
        $datatable->buildDatatable();

        return $this->render('RbsSalesBundle:Stock:index.html.twig', array(
            'datatable' => $datatable
        ));
    }

    /**
     * Lists all Category entities.
     *
     * @Route("/stocks_list_ajax", name="stocks_list_ajax", options={"expose"=true})
     * @Method("GET")
     */
    public function listAjaxAction()
    {
        $datatable = $this->get('rbs_erp.sales.datatable.stock');
        $datatable->buildDatatable();

        $query = $this->get('sg_datatables.query')->getQueryFrom($datatable);
        /** @var QueryBuilder $qb */
        $function = function($qb)
        {
            $qb->join("stocks.item", 'i');
            $qb->andWhere("stocks.deletedAt IS NULL");
        };
        $query->addWhereAll($function);
        return $query->getResponse();
    }

    /**
     * @Route("/stock-create", name="stock_create")
     * @Template("RbsSalesBundle:Stock:new.html.twig")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function createAction(Request $request)
    {
        $stock = new Stock();

        $form = $this->createForm(new StockForm(), $stock);

        if ('POST' === $request->getMethod()) {
            $form->handleRequest($request);

            if ($form->isValid()) {

                $this->getDoctrine()->getRepository('RbsSalesBundle:Stock')->create($stock);

                $this->get('session')->getFlashBag()->add(
                    'success',
                    'Stock Create Successfully!'
                );

                return $this->redirect($this->generateUrl('stocks_home'));
            }
        }

        return array(
            'form' => $form->createView()
        );
    }

//    /**
//     * @Route("/customer-update/{id}", name="customer_update", options={"expose"=true})
//     * @Template("RbsSalesBundle:Customer:update.html.twig")
//     * @param Request $request
//     * @param Customer $customer
//     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
//     */
//    public function updateAction(Request $request, Customer $customer)
//    {
//        $form = $this->createForm(new CustomerUpdateForm(), $customer);
//
//        if ('POST' === $request->getMethod()) {
//            $form->handleRequest($request);
//
//            if ($form->isValid()) {
//
//                $this->getDoctrine()->getRepository('RbsSalesBundle:Customer')->update($customer);
//
//                $this->get('session')->getFlashBag()->add(
//                    'success',
//                    'User Updated Successfully!'
//                );
//
//                return $this->redirect($this->generateUrl('customers_home'));
//            }
//        }
//
//        return array(
//            'form' => $form->createView()
//        );
//    }
//
//    /**
//     * @Route("/customer-update-password/{id}", name="customer_update_password", options={"expose"=true})
//     * @Template("RbsSalesBundle:Customer:update.password.html.twig")
//     * @param Request $request
//     * @param Customer $customer
//     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
//     */
//    public function updatePasswordAction(Request $request, Customer $customer)
//    {
//        $form = $this->createForm(new UserUpdatePasswordForm(), $customer->getUser());
//
//        if ($request->getMethod() == 'POST') {
//
//            $form->handleRequest($request);
//
//            if ($form->isValid()) {
//
//                $customer->getUser()->setPassword($form->get('plainPassword')->getData());
//                $customer->getUser()->setPlainPassword($form->get('plainPassword')->getData());
//
//                $this->getDoctrine()->getRepository('RbsUserBundle:User')->update($customer);
//
//                $this->get('session')->getFlashBag()->add(
//                    'notice',
//                    'Password Successfully Change'
//                );
//
//                return $this->redirect($this->generateUrl('customers_home'));
//            }
//        }
//
//        return array(
//            'form' => $form->createView()
//        );
//    }
//
//    /**
//     * @Route("/customer-details/{id}", name="customer_details", options={"expose"=true})
//     * @Template()
//     * @param Customer $customer
//     * @return \Symfony\Component\HttpFoundation\Response
//     */
//    public function detailsAction(Customer $customer)
//    {
//        return $this->render('RbsSalesBundle:Customer:details.html.twig', array(
//            'customer' => $customer
//        ));
//    }
//
//    /**
//     * @Route("/customer-delete/{id}", name="customer_delete", options={"expose"=true})
//     * @param Customer $customer
//     * @return \Symfony\Component\HttpFoundation\RedirectResponse
//     */
//    public function deleteAction(Customer $customer)
//    {
//        $customer->getUser()->getProfile()->removeFile($customer->getUser()->getProfile()->getPath());
//
//        $this->getDoctrine()->getManager()->remove($customer);
//        $this->getDoctrine()->getManager()->flush();
//
//        $this->get('session')->getFlashBag()->add(
//            'success',
//            'Customer Successfully Delete'
//        );
//
//        return $this->redirect($this->generateUrl('customers_home'));
//    }
}