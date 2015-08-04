<?php

namespace Rbs\Bundle\UserBundle\Controller;

use FOS\UserBundle\Controller\GroupController as Controller;
use FOS\UserBundle\Event\FilterGroupResponseEvent;
use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\Event\GroupEvent;
use FOS\UserBundle\FOSUserEvents;
use JMS\SecurityExtraBundle\Annotation as JMS;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Rbs\Bundle\UserBundle\Entity\Group;

/**
 * Group Controller.
 *
 */
class GroupController extends Controller
{
    /**
     * @Route("/groups", name="groups_home")
     * @Template()
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $request)
    {
        $groups = $this->getDoctrine()->getRepository('RbsUserBundle:Group')->groups();

        return $this->render('RbsUserBundle:Group:index.html.twig', array(
            'groups' => $groups
        ));
    }

    /**
     * @Route("/group-create", name="group_create")
     * @Template()
     * @param Request $request
     * @return null|RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function createAction(Request $request)
    {
        /** @var $groupManager \FOS\UserBundle\Model\GroupManagerInterface */
        $groupManager = $this->get('fos_user.group_manager');
        /** @var $formFactory \FOS\UserBundle\Form\Factory\FactoryInterface */
        $formFactory = $this->get('fos_user.group.form.factory');
        /** @var $dispatcher \Symfony\Component\EventDispatcher\EventDispatcherInterface */
        $dispatcher = $this->get('event_dispatcher');

        $group = $groupManager->createGroup('');

        $dispatcher->dispatch(FOSUserEvents::GROUP_CREATE_INITIALIZE, new GroupEvent($group, $request));

        $form = $formFactory->createForm();
        $form->setData($group);

        $form->handleRequest($request);

        if ($form->isValid()) {
            $event = new FormEvent($form, $request);
            $dispatcher->dispatch(FOSUserEvents::GROUP_CREATE_SUCCESS, $event);

            $groupManager->updateGroup($group);

            if (null === $response = $event->getResponse()) {
                $this->get('session')->getFlashBag()->add(
                    'success-status',
                    'Group Created Successfully!'
                );

                $url      = $this->container->get('router')->generate('groups_home');
                $response = new RedirectResponse($url);
            }

            $dispatcher->dispatch(FOSUserEvents::GROUP_CREATE_COMPLETED, new FilterGroupResponseEvent($group, $request, $response));

            return $response;
        }

        return $this->render('RbsUserBundle:Group:new.html.twig', array(
            'form' => $form->createView()
        ));
    }

    /**
     * @Route("/group-update/{id}", name="group_update")
     * @Template()
     * @param Request $request
     * @param Group $group
     * @return RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function groupUpdateAction(Request $request, Group $group)
    {
        $service = $this->get('fos_user.group.form.type');

        $form = $this->createForm($service, $group);

        if ('POST' === $request->getMethod()) {
            $form->handleRequest($request);

            if ($form->isValid()) {

                $this->getDoctrine()->getRepository('RbsUserBundle:Group')->update($group);

                $this->get('session')->getFlashBag()->add(
                    'success',
                    'Group Update Successfully!'
                );

                return $this->redirect($this->generateUrl('groups_home'));
            }
        }

        return $this->render('RbsUserBundle:Group:new.html.twig', array(
            'form' => $form->createView()
        ));
    }

    /**
     * @Route("/group-delete/{id}", name="group_delete")
     * @Template()
     * @param Request $request
     * @param Group $group
     * @return RedirectResponse
     */
    public function groupDeleteAction(Request $request, Group $group)
    {
        $this->container->get('fos_user.group_manager')->deleteGroup($group);

        $response = new RedirectResponse($this->container->get('router')->generate('groups_home'));

        $this->get('session')->getFlashBag()->add(
            'success',
            'Group Deleted Successfully!'
        );

        return $response;
    }

    /**
     * @Route("/group-details/{id}", name="group_details")
     * @Template()
     * @param Request $request
     * @param Group $group
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function detailsAction(Request $request, Group $group)
    {
        return $this->render('RbsUserBundle:Group:details.html.twig', array(
            'group' => $group
        ));
    }
}