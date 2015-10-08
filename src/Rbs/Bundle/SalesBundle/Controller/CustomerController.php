<?php

namespace Rbs\Bundle\SalesBundle\Controller;

use Doctrine\ORM\QueryBuilder;
use Rbs\Bundle\SalesBundle\Entity\Customer;
use Rbs\Bundle\SalesBundle\Form\Type\CustomerUpdateForm;
use Rbs\Bundle\UserBundle\Entity\User;
use Rbs\Bundle\UserBundle\Form\Type\UserUpdatePasswordForm;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use JMS\SecurityExtraBundle\Annotation as JMS;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Customer Controller.
 *
 */
class CustomerController extends BaseController
{
    /**
     * @Route("/customers", name="customers_home")
     * @Method("GET")
     * @Template()
     * @JMS\Secure(roles="ROLE_AGENT, ROLE_CUSTOMER_VIEW, ROLE_CUSTOMER_CREATE")
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
     * @JMS\Secure(roles="ROLE_AGENT, ROLE_CUSTOMER_VIEW, ROLE_CUSTOMER_CREATE")
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

        if ($this->isGranted('ROLE_AGENT')) {
            $query->getQuery()->andWhere('customers.agent = :agent')->setParameter('agent', $this->getUser());
        }

        return $query->getResponse();
    }

    /**
     * @Route("/customer/create", name="customer_create")
     * @Template("RbsSalesBundle:Customer:new.html.twig")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @JMS\Secure(roles="ROLE_CUSTOMER_CREATE")
     */
    public function createAction(Request $request)
    {
        $customer = new Customer();

        $service = $this->get('rbs_erp.sales.registration.form.type');

        $form = $this->createForm($service, $customer, array(
            'attr' => array('novalidate' => 'novalidate')
        ));

        if ('POST' === $request->getMethod()) {
            $form->handleRequest($request);

            if ($form->isValid()) {

                $customer->getUser()->setEnabled(1);
                $customer->getUser()->addRole('ROLE_CUSTOMER');
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
     * @Route("/customer/update/{id}", name="customer_update", options={"expose"=true})
     * @Template("RbsSalesBundle:Customer:update.html.twig")
     * @param Request $request
     * @param Customer $customer
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @JMS\Secure(roles="ROLE_CUSTOMER_CREATE")
     */
    public function updateAction(Request $request, Customer $customer)
    {
        $form = $this->createForm(new CustomerUpdateForm(), $customer, array(
            'attr' => array('novalidate' => 'novalidate')
        ));

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

    /**
     * @Route("/customer/update/password/{id}", name="customer_update_password", options={"expose"=true})
     * @Template("RbsSalesBundle:Customer:update.password.html.twig")
     * @param Request $request
     * @param User $user
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
     * @JMS\Secure(roles="ROLE_CUSTOMER")
     */
    public function updatePasswordAction(Request $request, User $user)
    {
        $form = $this->createForm(new UserUpdatePasswordForm(), $user);

        if ($request->getMethod() == 'POST') {

            $form->handleRequest($request);

            if ($form->isValid()) {

                $user->setPassword($form->get('plainPassword')->getData());
                $user->setPlainPassword($form->get('plainPassword')->getData());

                $this->getDoctrine()->getRepository('RbsUserBundle:User')->update($user);

                $this->get('session')->getFlashBag()->add(
                    'notice',
                    'Password Successfully Change'
                );

                return $this->redirect($this->generateUrl('homepage'));
            }
        }

        return array(
            'form' => $form->createView()
        );
    }

    /**
     * @Route("/customer/details/{id}", name="customer_details", options={"expose"=true})
     * @Template()
     * @param Customer $customer
     * @return \Symfony\Component\HttpFoundation\Response
     * @JMS\Secure(roles="ROLE_AGENT, ROLE_CUSTOMER_VIEW, ROLE_CUSTOMER_CREATE")
     */
    public function detailsAction(Customer $customer)
    {
        $this->checkViewDetailAccess($customer);

        $customerCurrentBalance = $this->getDoctrine()->getRepository('RbsSalesBundle:Customer')->getCurrentBalance($customer);

        return $this->render('RbsSalesBundle:Customer:details.html.twig', array(
            'customer' => $customer,
            'customerCurrentBalance' => $customerCurrentBalance
        ));
    }

    protected function checkViewDetailAccess(Customer $customer)
    {
        if ($this->isGranted('ROLE_AGENT')) {

            if ($customer->getAgent() != $this->getUser()) {
                throw new AccessDeniedException('Access Denied');
            }
        }
    }

    /**
     * @Route("/customer/delete/{id}", name="customer_delete", options={"expose"=true})
     * @param Customer $customer
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @JMS\Secure(roles="ROLE_CUSTOMER_CREATE")
     */
    public function deleteAction(Customer $customer)
    {
        $customer->getUser()->getProfile()->removeFile($customer->getUser()->getProfile()->getPath());

        $this->getDoctrine()->getManager()->remove($customer);
        $this->getDoctrine()->getManager()->flush();

        $this->get('session')->getFlashBag()->add(
            'success',
            'Customer Successfully Delete'
        );

        return $this->redirect($this->generateUrl('customers_home'));
    }

    /**
     * find customer ajax
     * @Route("find_customer_ajax", name="find_customer_ajax", options={"expose"=true})
     * @param Request $request
     * @return Response
     * @JMS\Secure(roles="ROLE_ORDER_VIEW, ROLE_CUSTOMER_VIEW, ROLE_CUSTOMER_CREATE")
     */
    public function findCustomerAction(Request $request)
    {
        $customerId = $request->request->get('customer');

        $customerRepo = $this->getDoctrine()->getRepository('RbsSalesBundle:Customer');
        $customer = $customerRepo->find($customerId);

        $response = new Response(json_encode(array(
            'creditLimit' => $customerRepo->getCurrentCreditLimit($customer),
            'balance' => $customerRepo->getCurrentBalance($customer)
        )));

        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    /**
     * @Route("/payment/customer-search", name="customer_search", options={"expose"=true})
     * @param Request $request
     * @return JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse
     * @JMS\Secure(roles="ROLE_CUSTOMER_VIEW, ROLE_CUSTOMER_CREATE")
     */
    public function getCustomers(Request $request)
    {
        $qb = $this->customerRepository()->createQueryBuilder('c');
        $qb->join('c.user', 'u');
        $qb->join('u.profile', 'p');
        $qb->select('u.id, p.fullName AS text');
        $qb->setMaxResults(100);

        if ($customerName = $request->query->get('q')) {
            $qb->where("p.fullName LIKE '%{$customerName}%'");
        }

        return new JsonResponse($qb->getQuery()->getResult());
    }
}