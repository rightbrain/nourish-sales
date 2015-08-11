<?php

namespace Rbs\Bundle\CoreBundle\Controller;

use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Rbs\Bundle\CoreBundle\Entity\CostHeader;
use Rbs\Bundle\CoreBundle\Form\Type\CostHeaderForm;

/**
 * CostHeader controller.
 *
 * @Route("/cost-header")
 */
class CostHeaderController extends BaseController
{

    /**
     * Lists all CostHeader entities.
     *
     * @Route("", name="cost_header")
     * @Method("GET")
     * @Template()
     */
    public function indexAction()
    {
        $datatable = $this->get('rbs_erp.core.datatable.cost_header');
        $datatable->buildDatatable();
        $deleteForm = $this->createDeleteForm(0);
        return array(
            'datatable' => $datatable,
            'deleteForm' => $deleteForm->createView()
        );
    }

    /**
     * Lists all Area entities.
     *
     * @Route("/cost_header_list_ajax", name="cost_header_list_ajax", options={"expose"=true})
     * @Method("GET")
     */
    public function listAjaxAction()
    {
        $datatable = $this->get('rbs_erp.core.datatable.cost_header');
        $datatable->buildDatatable();

        $query = $this->get('sg_datatables.query')->getQueryFrom($datatable);
        /** @var QueryBuilder $qb */
        $function = function($qb)
        {
            $qb->andWhere("cost_headers.deletedAt IS NULL");
        };
        $query->addWhereAll($function);

        return $query->getResponse();
    }

    /**
     * Creates a new CostHeader entity.
     *
     * @Route("/", name="cost_header_create")
     * @Method("POST")
     * @Template("RbsCoreBundle:CostHeader:new.html.twig")
     */
    public function createAction(Request $request)
    {
        $entity = new CostHeader();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();
            $this->flashMessage('success', 'Cost Header Created Successfully');
            return $this->redirect($this->generateUrl('cost_header'));
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Creates a form to create a CostHeader entity.
     *
     * @param CostHeader $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(CostHeader $entity)
    {
        $form = $this->createForm(new CostHeaderForm(), $entity, array(
            'action' => $this->generateUrl('cost_header_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Create'));

        return $form;
    }

    /**
     * Displays a form to create a new CostHeader entity.
     *
     * @Route("/new", name="cost_header_new")
     * @Method("GET")
     * @Template()
     */
    public function newAction()
    {
        $entity = new CostHeader();
        $form   = $this->createCreateForm($entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Finds and displays a CostHeader entity.
     *
     * @Route("/{id}", name="cost_header_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('RbsCoreBundle:CostHeader')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find CostHeader entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Displays a form to edit an existing CostHeader entity.
     *
     * @Route("/{id}/edit", name="cost_header_edit", options={"expose"=true})
     * @Method("GET")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('RbsCoreBundle:CostHeader')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find CostHeader entity.');
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
    * Creates a form to edit a CostHeader entity.
    *
    * @param CostHeader $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(CostHeader $entity)
    {
        $form = $this->createForm(new CostHeaderForm(), $entity, array(
            'action' => $this->generateUrl('cost_header_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }
    /**
     * Edits an existing CostHeader entity.
     *
     * @Route("/{id}", name="cost_header_update")
     * @Method("PUT")
     * @Template("RbsCoreBundle:CostHeader:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('RbsCoreBundle:CostHeader')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find CostHeader entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();
            $this->flashMessage('success', 'Cost Header Updated Successfully');
            return $this->redirect($this->generateUrl('cost_header'));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }
    /**
     * Deletes a CostHeader entity.
     *
     * @Route("/{id}", name="cost_header_delete", options={"expose"=true})
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('RbsCoreBundle:CostHeader')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find CostHeader entity.');
            }

            $this->flashMessage('success', 'Cost Header Deleted Successfully');
            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('cost_header'));
    }

    /**
     * Creates a form to delete a CostHeader entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder(null, array('attr' => array('id' => 'delete-form')))
            ->setAction($this->generateUrl('cost_header_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
