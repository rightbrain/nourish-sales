<?php

namespace Rbs\Bundle\CoreBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use JMS\SecurityExtraBundle\Annotation as JMS;

/**
 * Location controller.
 */
class LocationController extends BaseController
{

    /**
     * Lists all Location entities.
     *
     * @Route("/locations", name="locations")
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