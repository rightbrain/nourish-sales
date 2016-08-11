<?php

namespace Rbs\Bundle\CoreBundle\Controller;

use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Rbs\Bundle\CoreBundle\Entity\ItemType;
use Rbs\Bundle\CoreBundle\Form\Type\ItemTypeForm;
use JMS\SecurityExtraBundle\Annotation as JMS;

/**
 * ItemType controller.
 *
 * @Route("/itemtype")
 */
class ItemTypeController extends BaseController
{

    /**
     * Lists all ItemType entities.
     *
     * @Route("", name="itemtype")
     * @Method("GET")
     * @Template()
     * @JMS\Secure(roles="ROLE_ITEM_TYPE_MANAGE")
     */
    public function indexAction()
    {
        $datatable = $this->get('rbs_erp.core.datatable.item_type');
        $datatable->buildDatatable();
        $deleteForm = $this->createDeleteForm(0);
        return array(
            'datatable' => $datatable,
            'deleteForm' => $deleteForm->createView()
        );
    }

    /**
     * Lists all ItemType entities.
     *
     * @Route("/item_type_list_ajax", name="item_type_list_ajax", options={"expose"=true})
     * @Method("GET")
     * @JMS\Secure(roles="ROLE_ITEM_TYPE_MANAGE")
     */
    public function listAjaxAction()
    {
        $datatable = $this->get('rbs_erp.core.datatable.item_type');
        $datatable->buildDatatable();

        $query = $this->get('sg_datatables.query')->getQueryFrom($datatable);
        /** @var QueryBuilder $qb */
        $function = function($qb)
        {
            $qb->andWhere("item_types.deletedAt IS NULL");
        };
        $query->addWhereAll($function);

        return $query->getResponse();
    }

    /**
     * Creates a new ItemType entity.
     *
     * @Route("/", name="itemtype_create")
     * @Method("POST")
     * @Template("RbsCoreBundle:ItemType:new.html.twig")
     * @JMS\Secure(roles="ROLE_ITEM_TYPE_MANAGE")
     */
    public function createAction(Request $request)
    {
        $entity = new ItemType();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();
            $this->flashMessage('success', 'Item Type Created Successfully');
            return $this->redirect($this->generateUrl('itemtype'));
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Creates a form to create a ItemType entity.
     *
     * @param ItemType $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(ItemType $entity)
    {
        $form = $this->createForm(new ItemTypeForm(), $entity, array(
            'action' => $this->generateUrl('itemtype_create'),
            'method' => 'POST',
            'attr' => array(
                'novalidate' => 'novalidate'
            )
        ));

        $form->add('submit', 'submit', array('label' => 'Create'));

        return $form;
    }

    /**
     * Displays a form to create a new ItemType entity.
     *
     * @Route("/new", name="itemtype_new")
     * @Method("GET")
     * @Template()
     * @JMS\Secure(roles="ROLE_ITEM_TYPE_MANAGE")
     */
    public function newAction()
    {
        $entity = new ItemType();
        $form   = $this->createCreateForm($entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Finds and displays a ItemType entity.
     *
     * @Route("/{id}", name="itemtype_show")
     * @Method("GET")
     * @Template()
     * @JMS\Secure(roles="ROLE_ITEM_TYPE_MANAGE")
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('RbsCoreBundle:ItemType')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find ItemType entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Displays a form to edit an existing ItemType entity.
     *
     * @Route("/{id}/edit", name="itemtype_edit", options={"expose"=true})
     * @Method("GET")
     * @Template()
     * @JMS\Secure(roles="ROLE_ITEM_TYPE_MANAGE")
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('RbsCoreBundle:ItemType')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find ItemType entity.');
        }

        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
    * Creates a form to edit a ItemType entity.
    *
    * @param ItemType $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(ItemType $entity)
    {
        $form = $this->createForm(new ItemTypeForm(), $entity, array(
            'action' => $this->generateUrl('itemtype_update', array('id' => $entity->getId())),
            'method' => 'PUT',
            'attr' => array(
                'novalidate' => 'novalidate'
            )
        ));

        $form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }

    /**
     * Edits an existing ItemType entity.
     *
     * @Route("/{id}", name="itemtype_update")
     * @Method("PUT")
     * @Template("RbsCoreBundle:ItemType:edit.html.twig")
     * @JMS\Secure(roles="ROLE_ITEM_TYPE_MANAGE")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('RbsCoreBundle:ItemType')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find ItemType entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();
            $this->flashMessage('success', 'Item Type Updated Successfully');
            return $this->redirect($this->generateUrl('itemtype'));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Deletes a ItemType entity.
     *
     * @Route("/{id}", name="itemtype_delete", options={"expose"=true})
     * @Method("DELETE")
     * @JMS\Secure(roles="ROLE_ITEM_TYPE_MANAGE")
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('RbsCoreBundle:ItemType')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find ItemType entity.');
            }
            $this->flashMessage('success', 'Item Type Deleted Successfully');
            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('itemtype'));
    }

    /**
     * Creates a form to delete a ItemType entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder(null, array('attr' => array('id' => 'delete-form')))
            ->setAction($this->generateUrl('itemtype_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
