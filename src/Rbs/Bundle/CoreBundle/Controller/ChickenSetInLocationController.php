<?php

namespace Rbs\Bundle\CoreBundle\Controller;

use JMS\SecurityExtraBundle\Annotation as JMS;
use Rbs\Bundle\CoreBundle\Entity\ChickenSet;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

/**
 * ChickenSetInLocation controller.
 *
 */
class ChickenSetInLocationController extends BaseController
{
    /**
     * @Route("chicken/set/in/location", name="chicken_set_in_location")
     * @Template("RbsCoreBundle:Chicken:index.html.twig")
     * @return array
     */
    public function indexAction()
    {
        $chickenTypeSetArr = $this->getDoctrine()->getRepository('RbsCoreBundle:ChickenSet')->getAll();

        return array(
            'chickenTypeSetArr' => $chickenTypeSetArr,
        );
    }

    /**
     * @Route("chicken/set/in/location/add/{id}", name="chicken_set_in_location_add")
     * @Template("RbsCoreBundle:Chicken:add.html.twig")
     * @param ChickenSet $chickenSet
     * @return array
     */
    public function addAction(ChickenSet $chickenSet)
    {
        return array(
            'chickenSet' => $chickenSet
        );
    }

    /**
     * @Route("chicken/set/in/location/save/{id}", name="chicken_set_in_location_save")
     * @param Request $request
     * @param ChickenSet $chickenSet
     * @return array
     */
    public function saveAction(Request $request, ChickenSet $chickenSet)
    {
        $chickenSet->setQuantity($request->request->get('quantity'));
        $this->getDoctrine()->getRepository('RbsCoreBundle:ChickenSet')->update($chickenSet);

        return $this->redirect($this->generateUrl('chicken_set_in_location'));
    }
}
