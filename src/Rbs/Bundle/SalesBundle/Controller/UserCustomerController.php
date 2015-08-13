<?php

namespace Rbs\Bundle\SalesBundle\Controller;

use Doctrine\ORM\QueryBuilder;
use Rbs\Bundle\SalesBundle\Entity\Customer;
use Rbs\Bundle\SalesBundle\Form\Type\CustomerUpdateForm;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * User Controller.
 *
 */
class UserCustomerController extends Controller
{
    /**
     * @Route("/customers", name="customers_home")
     * @Method("GET")
     * @Template()
     */
    public function indexAction()
    {
        $customers = $this->getDoctrine()->getRepository('RbsSalesBundle:Customer')->customers();
        $datatable = $this->get('rbs_erp.sales.datatable.customer');
        $datatable->buildDatatable();

        return $this->render('RbsSalesBundle:Customer:index.html.twig', array(
            'customers' => $customers,
            'datatable' => $datatable
        ));
    }

    /**
     * Lists all Category entities.
     *
     * @Route("/customers_list_ajax", name="customers_list_ajax", options={"expose"=true})
     * @Method("GET")
     */
    public function listAjaxAction()
    {
        $datatable = $this->get('rbs_erp.sales.datatable.customer');
        $datatable->buildDatatable();

        $query = $this->get('sg_datatables.query')->getQueryFrom($datatable);
        /** @var QueryBuilder $qb */
        $function = function($qb)
        {
            $qb->andWhere("customers.deletedAt IS NULL");
        };
        $query->addWhereAll($function);
        return $query->getResponse();
    }

    /**
     * @Route("/customer-create", name="customer_create")
     * @Template("RbsSalesBundle:Customer:new.html.twig")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function createAction(Request $request)
    {
        $customer = new Customer();

        $service = $this->get('rbs_erp.sales.registration.form.type');

        $form = $this->createForm($service, $customer);

        if ('POST' === $request->getMethod()) {
            $form->handleRequest($request);

            if ($form->isValid()) {

                $this->getDoctrine()->getRepository('RbsSalesBundle:Customer')->create($customer);

                $this->get('session')->getFlashBag()->add(
                    'success',
                    'Customer Create Successfully!'
                );

                return $this->redirect($this->generateUrl('customers_home'));
            }
        }

        return array(
            'form' => $form->createView()
        );
    }

    /**
     * @Route("/customer-update/{id}", name="customer_update", options={"expose"=true})
     * @Template("RbsSalesBundle:Customer:update.html.twig")
     * @param Request $request
     * @param Customer $customer
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function updateAction(Request $request, Customer $customer)
    {
        $form = $this->createForm(new CustomerUpdateForm(), $customer);

        if ('POST' === $request->getMethod()) {
            $form->handleRequest($request);

            if ($form->isValid()) {

                $this->getDoctrine()->getRepository('RbsSalesBundle:Customer')->update($customer);

                $this->get('session')->getFlashBag()->add(
                    'success',
                    'User Updated Successfully!'
                );

                return $this->redirect($this->generateUrl('customers_home'));
            }
        }

        return array(
            'form' => $form->createView()
        );
    }
//
//    /**
//     * @Route("/user-update-password/{id}", name="user_update_password", options={"expose"=true})
//     * @Template("RbsUserBundle:User:update.password.html.twig")
//     * @param Request $request
//     * @param User $user
//     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
//     */
//    public function updatePasswordAction(Request $request, User $user)
//    {
//        $form = $this->createForm(new UserUpdatePasswordForm(), $user);
//
//        if ($request->getMethod() == 'POST') {
//
//            $form->handleRequest($request);
//
//            if ($form->isValid()) {
//
//                $user->setPassword($form->get('plainPassword')->getData());
//                $user->setPlainPassword($form->get('plainPassword')->getData());
//
//                $this->getDoctrine()->getRepository('RbsUserBundle:User')->update($user);
//
//                $this->get('session')->getFlashBag()->add(
//                    'notice',
//                    'Password Successfully Change'
//                );
//
//                return $this->redirect($this->generateUrl('users_home'));
//            }
//        }
//
//        return array(
//            'form' => $form->createView()
//        );
//    }
//
//    /**
//     * @Route("/user-enabled/{id}", name="user_enabled", options={"expose"=true})
//     * @param User $user
//     * @return \Symfony\Component\HttpFoundation\RedirectResponse
//     */
//    public function userEnabledAction(User $user)
//    {
//        $enabled = $this->isUserEnabled($user);
//
//        $user->setEnabled($enabled);
//
//        $this->getDoctrine()->getRepository('RbsUserBundle:User')->update($user);
//
//        $this->get('session')->getFlashBag()->add(
//            'success',
//            'User Successfully Enabled'
//        );
//
//        return $this->redirect($this->generateUrl('users_home'));
//    }
//
//    /**
//     * @param User $user
//     * @return int
//     */
//    protected function isUserEnabled(User $user)
//    {
//        if ($user->isEnabled()) {
//            $enabled = 0;
//            return $enabled;
//        } else {
//            $enabled = 1;
//            return $enabled;
//        }
//    }
//
//    /**
//     * @Route("/user-details/{id}", name="user_details", options={"expose"=true})
//     * @Template()
//     * @param Request $request
//     * @param User $user
//     * @return \Symfony\Component\HttpFoundation\Response
//     */
//    public function detailsAction(User $user)
//    {
//        return $this->render('RbsUserBundle:User:details.html.twig', array(
//            'user' => $user
//        ));
//    }

    /**
     * @Route("/customer-delete/{id}", name="customer_delete", options={"expose"=true})
     * @param Customer $customer
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteAction(Customer $customer)
    {
        $this->getDoctrine()->getManager()->remove($customer);
        $this->getDoctrine()->getManager()->flush();

        $this->get('session')->getFlashBag()->add(
            'success',
            'Customer Successfully Delete'
        );

        return $this->redirect($this->generateUrl('customers_home'));
    }
}