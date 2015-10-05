<?php

namespace Rbs\Bundle\CoreBundle\Controller;

use Rbs\Bundle\SalesBundle\Entity\Sms;
use Rbs\Bundle\SalesBundle\Helper\SmsParse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class DefaultController extends BaseController
{
    /**
     * @Route("/", name="homepage", options={"expose"=true})
     * @Template()
     */
    public function indexAction()
    {
        return array();
    }

    /**
     * @Route("/hello2", name="homepage2")
     * @Template("RbsCoreBundle:Default:index.html.twig")
     */
    public function index2Action()
    {
        return array();
    }

}
