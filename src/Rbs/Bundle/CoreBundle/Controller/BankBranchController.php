<?php

namespace Rbs\Bundle\CoreBundle\Controller;

use Rbs\Bundle\CoreBundle\Entity\TransportIncentive;
use Rbs\Bundle\CoreBundle\Entity\Upload;
use Rbs\Bundle\CoreBundle\Form\Type\UploadForm;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Rbs\Bundle\CoreBundle\Entity\BankBranch;
use Rbs\Bundle\CoreBundle\Form\Type\BankBranchForm;
use JMS\SecurityExtraBundle\Annotation as JMS;

/**
 * BankBranch controller.
 *
 * @Route("/settings/bank-branch")
 */
class BankBranchController extends BaseController
{

    /**
     * Lists all BankBranch entities.
     *
     * @Route("", name="bankbranch")
     * @Method("GET")
     * @Template()
     * @JMS\Secure(roles="ROLE_HEAD_OFFICE_USER")
     */
    public function indexAction()
    {
        $datatable = $this->get('rbs_erp.core.datatable.bank_branch');
        $datatable->buildDatatable();
        return array(
            'datatable' => $datatable,
        );
    }

    /**
     * @Route("/list_ajax", name="bank_branch_list_ajax", options={"expose"=true})
     * @Method("GET")
     * @JMS\Secure(roles="ROLE_HEAD_OFFICE_USER")
     */
    public function listAjaxAction()
    {
        $datatable = $this->get('rbs_erp.core.datatable.bank_branch');
        $datatable->buildDatatable();

        $query = $this->get('sg_datatables.query')->getQueryFrom($datatable);
        /** @var QueryBuilder $qb */
        $function = function($qb)
        {
        };
        $query->addWhereAll($function);

        return $query->getResponse();
    }

    /**
     * Creates a new BankBranch entity.
     *
     * @Route("/create", name="bankbranch_create")
     * @Template("RbsCoreBundle:BankBranch:new.html.twig")
     * @JMS\Secure(roles="ROLE_HEAD_OFFICE_USER")
     */
    public function createAction(Request $request)
    {
        $entity = new BankBranch();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirectToRoute('bankbranch');
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Creates a form to create a BankBranch entity.
     *
     * @param BankBranch $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(BankBranch $entity)
    {
        $form = $this->createForm(new BankBranchForm(), $entity, array(
            'action' => $this->generateUrl('bankbranch_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Create'));

        return $form;
    }


    /**
    * Creates a form to edit a BankBranch entity.
    *
    * @param BankBranch $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(BankBranch $entity)
    {
        $form = $this->createForm(new BankBranchForm(), $entity, array(
            'action' => $this->generateUrl('bankbranch_update', array('id' => $entity->getId())),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }
    /**
     * Edits an existing BankBranch entity.
     *
     * @Route("/{id}/edit", name="bankbranch_update", options={"expose"=true})
     * @Template("RbsCoreBundle:BankBranch:new.html.twig")
     * @JMS\Secure(roles="ROLE_HEAD_OFFICE_USER")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('RbsCoreBundle:BankBranch')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find BankBranch entity.');
        }

        $form = $this->createEditForm($entity);

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $em->flush();

                return $this->redirectToRoute('bankbranch');
            }
        }

        return array(
            'entity'      => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Deletes a BankBranch entity.
     *
     * @Route("/{id}", name="bankbranch_delete")
     * @Method("DELETE")
     * @JMS\Secure(roles="ROLE_ADMIN")
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('RbsCoreBundle:BankBranch')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find BankBranch entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('settings_bankbranch'));
    }

    /**
     * Deletes a BankBranch entity.
     *
     * @Route("/find/branch/by/bank/{id}", name="branch_by_bank", options={"expose"=true})
     * @JMS\Secure(roles="ROLE_ADMIN")
     * @param $id
     * @Method("GET")
     * @return JsonResponse
     */
    public function getBranchByBankAction($id)
    {
        $returnArray = array();
        if($id){
            $branches = $this->getDoctrine()->getRepository('RbsCoreBundle:BankBranch')->findBranchByBank($id);

            if($branches){
                foreach ($branches as $key=>$branch){
                    $returnArray[]= array('id'=>$key, 'name'=>$branch);
                }
            }
        }

        return new JsonResponse($returnArray);
    }

    /**
     * Creates a form to delete a BankBranch entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('settings_bankbranch_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm()
        ;
    }

    /**
     * @Route("/bank/branch/import", name="bank_branch_import")
     * @Template("RbsCoreBundle:TransportIncentive:import-form.html.twig")
     * @param Request $request
     * @return array|\Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function importCsv(Request $request)
    {
        $upload = new Upload();
        $form = $this->createForm(new UploadForm(), $upload);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            set_time_limit(0);
            ini_set('memory_limit', '1014M');
            $file = $upload->getFile();
            $fileName = md5(uniqid()) . '_trans_commission.csv';
            $file->move(
                $this->getParameter('brochures_directory'),
                $fileName
            );
            $upload->setFile($fileName);

            $file = $this->get('request')->getSchemeAndHttpHost() . '/uploads/sales/csv-import/' . $fileName;
            $data = $this->getCSVFileData($file);
            $i = 0;
            $j = 1;

            foreach ($data as $col) {
                if ($i == 0) {
                    $i++;
                    continue;
                }

                $bankBranchName = $col[0];
                $mobile = $col[1];
                $branchCode = $col[2];
                $bankSlug = $col[3];

                if ($bankBranchName == '' || $bankSlug == '') {
                    continue;
                }

                $bank = $this->getDoctrine()->getRepository('RbsCoreBundle:Bank')->findOneBy(array('slug' => $bankSlug));

                $existBranch= $this->getDoctrine()->getRepository('RbsCoreBundle:BankBranch')->findOneBy(array('name'=>strtoupper($bankBranchName),'bank'=>$bank));

                if($existBranch){
                    $branch=$existBranch;
                }else{
                    $branch= new BankBranch();
                }
                $branch->setBank($bank?$bank:null);
                $branch->setBranchCode($branchCode);
                $branch->setMobile($mobile);
                $branch->setName($bankBranchName);
                $em->persist($branch);
                $em->flush();

                $i++;
            }

            $this->flashMessage('success', 'Branch imported successfully');
            return $this->redirect($this->generateUrl('bankbranch'));
        }

        return array(
            'form' => $form->createView()
        );
    }


    public function getCSVFileData($webPath) {
        $fileData = array();
        $file = fopen($webPath,"r");
        while(! feof($file)) {
            $fileData[] = fgetcsv($file, 1024);
        }
        fclose($file);
        return $fileData;
    }
}
