<?php

namespace Rbs\Bundle\CoreBundle\Controller;

use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Rbs\Bundle\CoreBundle\Entity\ProjectType;
use Rbs\Bundle\CoreBundle\Form\Type\ProjectTypeForm;

/**
 * ProjectType controller.
 *
 * @Route("/projecttype")
 */
class ProjectTypeController extends BaseController
{

    /**
     * Lists all ProjectType entities.
     *
     * @Route("", name="projecttype")
     * @Method("GET")
     * @Template()
     */
    public function indexAction()
    {
        $datatable = $this->get('rbs_erp.core.datatable.project_type');
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
     * @Route("/project_type_list_ajax", name="project_type_list_ajax", options={"expose"=true})
     * @Method("GET")
     */
    public function listAjaxAction()
    {
        $datatable = $this->get('rbs_erp.core.datatable.project_type');
        $datatable->buildDatatable();

        $query = $this->get('sg_datatables.query')->getQueryFrom($datatable);
        /** @var QueryBuilder $qb */
        $function = function($qb)
        {
            $qb->andWhere("project_types.deletedAt IS NULL");
        };
        $query->addWhereAll($function);

        return $query->getResponse();
    }

    /**
     * Creates a new ProjectType entity.
     *
     * @Route("/", name="projecttype_create")
     * @Method("POST")
     * @Template("RbsCoreBundle:ProjectType:new.html.twig")
     */
    public function createAction(Request $request)
    {
        $entity = new ProjectType();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();
            $this->flashMessage('success', 'Project Type Created Successfully');
            return $this->redirect($this->generateUrl('projecttype'));
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Creates a form to create a ProjectType entity.
     *
     * @param ProjectType $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(ProjectType $entity)
    {
        $form = $this->createForm(new ProjectTypeForm(), $entity, array(
            'action' => $this->generateUrl('projecttype_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Create'));

        return $form;
    }

    /**
     * Displays a form to create a new ProjectType entity.
     *
     * @Route("/new", name="projecttype_new")
     * @Method("GET")
     * @Template()
     */
    public function newAction()
    {
        $entity = new ProjectType();
        $form   = $this->createCreateForm($entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Finds and displays a ProjectType entity.
     *
     * @Route("/{id}", name="projecttype_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('RbsCoreBundle:ProjectType')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find ProjectType entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Displays a form to edit an existing ProjectType entity.
     *
     * @Route("/{id}/edit", name="projecttype_edit", options={"expose"=true})
     * @Method("GET")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('RbsCoreBundle:ProjectType')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find ProjectType entity.');
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
    * Creates a form to edit a ProjectType entity.
    *
    * @param ProjectType $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(ProjectType $entity)
    {
        $form = $this->createForm(new ProjectTypeForm(), $entity, array(
            'action' => $this->generateUrl('projecttype_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }
    /**
     * Edits an existing ProjectType entity.
     *
     * @Route("/{id}", name="projecttype_update")
     * @Method("PUT")
     * @Template("RbsCoreBundle:ProjectType:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('RbsCoreBundle:ProjectType')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find ProjectType entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();
            $this->flashMessage('success', 'Project Type Updated Successfully');
            return $this->redirect($this->generateUrl('projecttype'));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }
    /**
     * Deletes a ProjectType entity.
     *
     * @Route("/{id}", name="projecttype_delete", options={"expose"=true})
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('RbsCoreBundle:ProjectType')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find ProjectType entity.');
            }
            $this->flashMessage('success', 'Project Type Deleted Successfully');
            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('projecttype'));
    }

    /**
     * Creates a form to delete a ProjectType entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder(null, array('attr' => array('id' => 'delete-form')))
            ->setAction($this->generateUrl('projecttype_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
