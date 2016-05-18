<?php

namespace Rbs\Bundle\SalesBundle\Controller;

use Doctrine\ORM\QueryBuilder;
use Rbs\Bundle\SalesBundle\Entity\Target;
use Rbs\Bundle\SalesBundle\Form\Type\TargetForm;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use JMS\SecurityExtraBundle\Annotation as JMS;

/**
 * Target Controller.
 *
 */
class TargetController extends BaseController
{
    /**
     * @Route("/target/list", name="target_list", options={"expose"=true})
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function stockHistoryAllAction(Request $request)
    {
        $datatable = $this->get('rbs_erp.sales.datatable.target');
        $datatable->buildDatatable();

        return $this->render('RbsSalesBundle:Target:index.html.twig', array(
            'datatable' => $datatable
        ));
    }

    /**
     * Lists all Target entities.
     *
     * @Route("/target_list_ajax", name="target_list_ajax", options={"expose"=true})
     * @Method("GET")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listAjaxAction(Request $request)
    {
        $datatable = $this->get('rbs_erp.sales.datatable.target');
        $datatable->buildDatatable();

        $query = $this->get('sg_datatables.query')->getQueryFrom($datatable);
        /** @var QueryBuilder $qb
         * @param $qb
         */
        $function = function($qb)
        {
            $qb->where('targets.quantity > 0');
            $qb->orderBy('targets.createdAt', 'desc');
        };
        $query->addWhereAll($function);
        return $query->getResponse();
    }

    /**
     * @Route("/target/create", name="target_create", options={"expose"=true})
     * @Template("RbsSalesBundle:Target:new.html.twig")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function createAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $subCategories = $em->getRepository('RbsCoreBundle:SubCategory')->findAll();

        $target = new Target();

        foreach ($subCategories as $subCategory) {
            $subCategoryWiseField = new Target();
            $subCategoryWiseField->setQuantity(0);
            $subCategoryWiseField->setSubCategory($subCategory);
            $targets[] = $subCategoryWiseField;
            $sc[] = $subCategory->getSubCategoryName();
        }

        $form = $this->createForm(new TargetForm(), $target, array(
            'action' => $this->generateUrl('target_create'), 'method' => 'POST',
            'attr' => array('novalidate' => 'novalidate')
        ));

        $form->get('child_entities')->setData($targets);

        if ('POST' === $request->getMethod()) {

            $form->handleRequest($request);

            if ($form->isValid()) {
                $em->getRepository('RbsSalesBundle:Target')->create($target);
                $this->flashMessage('success', 'Target Add Successfully!');
                return $this->redirect($this->generateUrl('target_list'));
            }
        }
        return array(
            'form' => $form->createView(),
            'subCategories' => $sc
        );
    }
}