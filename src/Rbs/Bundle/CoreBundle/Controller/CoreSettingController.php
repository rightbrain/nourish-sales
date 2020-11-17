<?php

namespace Rbs\Bundle\CoreBundle\Controller;

use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use JMS\SecurityExtraBundle\Annotation as JMS;

/**
 * Vendor controller.
 *
 * @Route("/core/setting")
 */
class CoreSettingController extends Controller
{
    /**
     * @Route("/", name="core_setting")
     * @Method("GET")
     * @JMS\Secure(roles="ROLE_CORE_SETTING")
     */
    public function indexAction()
    {
        $entities = $this->getDoctrine()->getRepository("RbsCoreBundle:CoreSettings")->findAll();
        return $this->render('RbsCoreBundle:CoreSetting:index.html.twig', array(
            'entities' => $entities,
        ));
    }

    /**
     * @Route("/{id}/status/change", name="setting_status_change")
     * @Method("POST")
     * @JMS\Secure(roles="ROLE_CORE_SETTING")
     */
    public function statusChangeAction(Request $request, $id)
    {
        $data = $request->request->get('status');
        $entity = $this->getDoctrine()->getRepository("RbsCoreBundle:CoreSettings")->find($id);

        $entity->setStatus($data);

        $em = $this->getDoctrine()->getEntityManager();

        $em->persist($entity);
        $em->flush();

        return new JsonResponse(
            array(
                'status'=>200,
                'message'=>"Success",
            )
        );
    }
    /**
     * @Route("/refresh", name="setting_list_refresh", options={"expose"=true})
     * @Method("GET")
     * @JMS\Secure(roles="ROLE_CORE_SETTING")
     */
    public function refreshAction()
    {
        $entities = $this->getDoctrine()->getRepository("RbsCoreBundle:CoreSettings")->findAll();
        return $this->render('RbsCoreBundle:CoreSetting:table-body.html.twig', array(
            'entities' => $entities
            )
        );
    }

}
