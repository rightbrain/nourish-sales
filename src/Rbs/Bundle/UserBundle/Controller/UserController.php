<?php

namespace Rbs\Bundle\UserBundle\Controller;

use Doctrine\ORM\QueryBuilder;
use Rbs\Bundle\SalesBundle\Entity\Agent;
use Rbs\Bundle\UserBundle\Entity\User;
use Rbs\Bundle\UserBundle\Form\Type\UserUpdateForm;
use Rbs\Bundle\UserBundle\Form\Type\UserUpdatePasswordForm;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use JMS\SecurityExtraBundle\Annotation as JMS;

/**
 * User Controller.
 *
 */
class UserController extends Controller
{
    /**
     * @Route("/users", name="users_home")
     * @Method("GET")
     * @Template()
     * @JMS\Secure(roles="ROLE_USER_VIEW, ROLE_USER_CREATE, ROLE_ADMIN")
     */
    public function indexAction()
    {
        $users = $this->getDoctrine()->getRepository('RbsUserBundle:User')->users();
        $datatable = $this->get('rbs_erp.user.datatable.user');
        $datatable->buildDatatable();

        return $this->render('RbsUserBundle:User:index.html.twig', array(
            'users' => $users,
            'datatable' => $datatable
        ));
    }

    /**
     * Lists all Category entities.
     *
     * @Route("/users_list_ajax", name="users_list_ajax", options={"expose"=true})
     * @Method("GET")
     * @JMS\Secure(roles="ROLE_USER_VIEW, ROLE_USER_CREATE, ROLE_ADMIN")
     */
    public function listAjaxAction()
    {
        $datatable = $this->get('rbs_erp.user.datatable.user');
        $datatable->buildDatatable();

        $query = $this->get('sg_datatables.query')->getQueryFrom($datatable);
        /** @var QueryBuilder $qb */
        $function = function($qb)
        {
//            $qb->where("users.userType NOT IN('AGENT')");
            $qb->andWhere("users.deletedAt IS NULL");
        };
        $query->addWhereAll($function);

        return $query->getResponse();
    }

    /**
     * @Route("/user/create", name="user_create")
     * @Template("RbsUserBundle:User:new.html.twig")
     * @param Request $request
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @JMS\Secure(roles="ROLE_USER_CREATE, ROLE_ADMIN")
     */
    public function createAction(Request $request)
    {
        $user = new User();
        $agent = new Agent();

        $service = $this->get('rbs_user.registration.form.type');

        $form = $this->createForm($service, $user);

        if ('POST' === $request->getMethod()) {
            $form->handleRequest($request);

            if ($form->isValid()) {

                $user->setEnabled(true);
                $this->getDoctrine()->getRepository('RbsUserBundle:User')->create($user);
                if($request->request->get('user')['userType'] == User::AGENT){
                    $agent->setUser($this->getDoctrine()->getRepository('RbsUserBundle:User')->find($user->getId()));
                    $agent->setAgentID($user->getId());
                    $this->getDoctrine()->getRepository('RbsSalesBundle:Agent')->create($agent);
                }

                $this->get('session')->getFlashBag()->add(
                    'success',
                    'User Create Successfully!'
                );

                return $this->redirect($this->generateUrl('users_home'));
            }
        }

        return array(
            'form' => $form->createView()
        );
    }

    /**
     * @Route("/user/update/{id}", name="user_update", options={"expose"=true})
     * @Template("RbsUserBundle:User:update.html.twig")
     * @param Request $request
     * @param User $user
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @JMS\Secure(roles="ROLE_USER_CREATE, ROLE_ADMIN")
     */
    public function updateAction(Request $request, User $user)
    {
        $form = $this->createForm(new UserUpdateForm(), $user);

        if ('POST' === $request->getMethod()) {
            $form->handleRequest($request);

            if ($form->isValid()) {

                $this->getDoctrine()->getRepository('RbsUserBundle:User')->update($user);
                if($request->request->get('user')['userType'] == User::AGENT){
                    $agent = $this->getDoctrine()->getRepository('RbsSalesBundle:Agent')->findOneBy(array('user'=>$user->getId()));
                    if($agent == false){
                        $agent = new Agent();
                        $agent->setUser($this->getDoctrine()->getRepository('RbsUserBundle:User')->find($user->getId()));
                        $agent->setAgentID($user->getId());
                        $this->getDoctrine()->getRepository('RbsSalesBundle:Agent')->create($agent);
                    }
                }
                
                $this->get('session')->getFlashBag()->add(
                    'success',
                    'User Updated Successfully!'
                );

                return $this->redirect($this->generateUrl('users_home'));
            }
        }

        return array(
            'form' => $form->createView(),
            'user' => $user
        );
    }

    /**
     * @Route("/user/update/password/{id}", name="user_update_password", options={"expose"=true})
     * @Template("RbsUserBundle:User:update.password.html.twig")
     * @param Request $request
     * @param User $user
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     * @JMS\Secure(roles="ROLE_USER_CREATE, ROLE_ADMIN")
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

                return $this->redirect($this->generateUrl('users_home'));
            }
        }

        return array(
            'form' => $form->createView()
        );
    }

    /**
     * @Route("/user/enabled/{id}", name="user_enabled", options={"expose"=true})
     * @param User $user
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @JMS\Secure(roles="ROLE_USER_CREATE, ROLE_ADMIN")
     */
    public function userEnabledAction(User $user)
    {
        $enabled = $this->isUserEnabled($user);

        $user->setEnabled($enabled);

        $this->getDoctrine()->getRepository('RbsUserBundle:User')->update($user);

        $this->get('session')->getFlashBag()->add(
            'success',
            'User Successfully Enabled'
        );

        return $this->redirect($this->generateUrl('users_home'));
    }

    /**
     * @param User $user
     * @return int
     */
    protected function isUserEnabled(User $user)
    {
        if ($user->isEnabled()) {
            $enabled = 0;
            return $enabled;
        } else {
            $enabled = 1;
            return $enabled;
        }
    }

    /**
     * @Route("/user/details/{id}", name="user_details", options={"expose"=true})
     * @Template()
     * @param User $user
     * @return \Symfony\Component\HttpFoundation\Response
     * @JMS\Secure(roles="ROLE_USER_VIEW, ROLE_USER_CREATE, ROLE_ADMIN")
     */
    public function detailsAction(User $user)
    {
        return $this->render('RbsUserBundle:User:details.html.twig', array(
            'user' => $user
        ));
    }

    /**
     * @Route("/user/delete/{id}", name="user_delete", options={"expose"=true})
     * @param User $user
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @JMS\Secure(roles="ROLE_USER_CREATE, ROLE_ADMIN")
     */
    public function deleteAction(User $user)
    {
        if($user->getProfile()->getPath()){
            $user->getProfile()->removeFile($user->getProfile()->getPath());
        }

        $this->getDoctrine()->getRepository('RbsUserBundle:User')->delete($user);

        $this->get('session')->getFlashBag()->add(
            'success',
            'User Successfully Delete'
        );

        return $this->redirect($this->generateUrl('users_home'));
    }
}