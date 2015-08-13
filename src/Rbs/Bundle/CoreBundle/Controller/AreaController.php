<?php

namespace Rbs\Bundle\CoreBundle\Controller;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\QueryBuilder;
use Rbs\Bundle\CoreBundle\Entity\Address;
use Rbs\Bundle\CoreBundle\Form\Type\AreaForm;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Rbs\Bundle\CoreBundle\Entity\Area;
use Symfony\Component\HttpFoundation\Response;

/**
 * Area controller.
 *
 * @Route("/area")
 */
class AreaController extends BaseController
{

    /**
     * Lists all Area entities.
     *
     * @Route("", name="area")
     * @Method("GET")
     * @Template()
     */
    public function indexAction()
    {
        /*set_time_limit(0);
        $em = $this->getDoctrine()->getManager();
        $addressRepo = $em->getRepository('RbsCoreBundle:Address');
        $addresses = $addressRepo->findAll();
        $vendor = $em->getRepository('RbsCoreBundle:Vendor')->find(1);
        foreach ($addresses as $address) {
            $area = false;
            switch ($address->getLevel()) {
                case 2:
                    $area = new Area();
                    $area->setLevel1($address);
                    $area->setAreaName($address->getName());
                    break;
                case 3:
                    $area = new Area();
                    $l1 = $addressRepo->findOneBy(array('id' => $address->getC4()));
                    $area->setLevel1($l1);
                    $area->setLevel2($address);
                    $area->setAreaName($address->getName() . ', '. $l1->getName());
                    break;
                case 4:
                    $area = new Area();
                    $l1 = $addressRepo->findOneBy(array('id' => $address->getC4()));
                    $area->setLevel1($l1);
                    $l2 = $addressRepo->findOneBy(array('id' => $l1->getC4()));
                    $area->setLevel2($l2);
                    $area->setLevel3($address);
                    $area->setAreaName($address->getName().', '.$l2->getName().', '.$l1->getName());
                    break;

            }
            if ($area) {
                //$vendor->setArea($area);
                $area->setStatus(0);
                $em->persist($area);
                $em->flush();
                $em->clear($area);
                $em->clear($address);
            }
        }*/

        $datatable = $this->get('rbs_erp.core.datatable.area');
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
     * @Route("/area_list_ajax", name="area_list_ajax", options={"expose"=true})
     * @Method("GET")
     */
    public function listAjaxAction()
    {
        $datatable = $this->get('rbs_erp.core.datatable.area');
        $datatable->buildDatatable();

        $query = $this->get('sg_datatables.query')->getQueryFrom($datatable);
        /** @var QueryBuilder $qb */
        $function = function($qb)
        {
            $qb->andWhere("areas.deletedAt IS NULL");
        };
        $query->addWhereAll($function);

        return $query->getResponse();
    }

    /**
     * Creates a new Area entity.
     *
     * @Route("/", name="area_create")
     * @Method("POST")
     * @Template("RbsCoreBundle:Area:new.html.twig")
     */
    public function createAction(Request $request)
    {
        $entity = new Area();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            $this->flashMessage('success', 'Area Created Successfully');
            return $this->redirect($this->generateUrl('area'));
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Creates a form to create a Area entity.
     *
     * @param Area $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(Area $entity)
    {
        $form = $this->createForm(new AreaForm($this->get('request')), $entity, array(
            'action' => $this->generateUrl('area_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Create'));

        return $form;
    }

    /**
     * Displays a form to create a new Area entity.
     *
     * @Route("/new", name="area_new")
     * @Method("GET")
     * @Template()
     */
    public function newAction()
    {
        $entity = new Area();
        $form   = $this->createCreateForm($entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Finds and displays a Area entity.
     *
     * @Route("/{id}", name="area_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('RbsCoreBundle:Area')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Area entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Displays a form to edit an existing Area entity.
     *
     * @Route("/{id}/edit", name="area_edit", options={"expose"=true})
     * @Method("GET")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('RbsCoreBundle:Area')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Area entity.');
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
    * Creates a form to edit a Area entity.
    *
    * @param Area $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(Area $entity)
    {
        $form = $this->createForm(new AreaForm($this->get('request')), $entity, array(
            'action' => $this->generateUrl('area_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }

    /**
     * Edits an existing Area entity.
     *
     * @Route("/{id}", name="area_update")
     * @Method("PUT")
     * @Template("RbsCoreBundle:Area:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('RbsCoreBundle:Area')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Area entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            $this->flashMessage('success', 'Area Updated Successfully');
            return $this->redirect($this->generateUrl('area'));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Deletes a Area entity.
     *
     * @Route("/{id}", name="area_delete", options={"expose"=true})
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('RbsCoreBundle:Area')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Area entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('area'));
    }

    /**
     * Creates a form to delete a Area entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder(null, array('attr' => array('id' => 'delete-form')))
            ->setAction($this->generateUrl('area_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }

    /**
     *
     * @Route("/filter-area/", name="area_filter", options={"expose"=true})
     */
    public function areaFilterAction(Request $request)
    {
        $areas = array();
        if ($request->query->get('id')) {
            $areas = $this->getDoctrine()->getRepository('RbsCoreBundle:Address')->findBy(
                array(
                    'c4' => $request->query->get('id')
                )
            );
        }

        $html = '<option value="">Choose an option</option>';
        foreach ($areas as $r) {
            $html .= '<option value="'.$r->getId().'">'.$r->getName().'</option>';
        }

        return new Response($html);
    }

}
