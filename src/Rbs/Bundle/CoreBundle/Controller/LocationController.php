<?php

namespace Rbs\Bundle\CoreBundle\Controller;

use JMS\SecurityExtraBundle\Annotation as JMS;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Location controller.
 *
 * @Route("/location")
 */
class LocationController extends BaseController
{
    
    /**
     *
     * @Route("/filter-location/", name="location_filter", options={"expose"=true})
     * @JMS\Secure(roles="ROLE_LOCATION_MANAGE")
     */
    public function areaFilterAction(Request $request)
    {
        $locations = array();
        if ($request->query->get('id')) {
            $locations = $this->getDoctrine()->getRepository('RbsCoreBundle:Location')->findBy(
                array(
                    'parentId' => $request->query->get('id')
                )
            );
        }

        $html = '<option value="">Choose an option</option>';
        foreach ($locations as $r) {
            $html .= '<option value="'.$r->getId().'">'.$r->getName().'</option>';
        }

        return new Response($html);
    }

    /**
     * Lists all Location entities.
     *
     * @Route("", name="locations")
     * @Template("RbsCoreBundle:Location:index.html.twig")
     * @JMS\Secure(roles="ROLE_ADMIN, ROLE_SUPER_ADMIN")
     * @return array
     */
    public function indexAction()
    {
        $locations = $this->getDoctrine()->getRepository('RbsCoreBundle:Location')->getAllFromSectorToUpozilla();

        $locationsSectors = $this->getDoctrine()->getRepository('RbsCoreBundle:Location')->getSectors();
        $locationsZones = $this->getDoctrine()->getRepository('RbsCoreBundle:Location')->getZones();
        $locationsRegions = $this->getDoctrine()->getRepository('RbsCoreBundle:Location')->getRegions();
        $locationsDistricts = $this->getDoctrine()->getRepository('RbsCoreBundle:Location')->getDistricts();
        $locationsUpozillas = $this->getDoctrine()->getRepository('RbsCoreBundle:Location')->getUpozillas();

        return array(
            'locations' => $locations,
            'locationsSectors' => $locationsSectors,
            'locationsZones' => $locationsZones,
            'locationsRegions' => $locationsRegions,
            'locationsDistricts' => $locationsDistricts,
            'locationsUpozillas' => $locationsUpozillas
        );
    }

}
