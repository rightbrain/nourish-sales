<?php

namespace Rbs\Bundle\CoreBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class DefaultController extends BaseController
{
    /**
     * @Route("/hello", name="homepage", options={"expose"=true})
     * @Template()
     */
    public function indexAction()
    {
        return array();
    }

    /**
     * @Route("/hello2", name="homepage2")
     * @Template("RbsCoreBundle:Default:index.html.twig", vars={"post"})
     */
    public function index2Action()
    {
        return array();
    }

    /**
     * @Route("/hello3", name="homepage3")
     * @Template()
     */
    public function index3Action()
    {
        return array();
    }
}
