<?php

namespace Rbs\Bundle\CoreBundle\Controller;

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
        $creditLimitNotification = $this->getDoctrine()->getRepository('RbsSalesBundle:CreditLimit')->creditLimitNotificationCount();
        
        return array(
            'creditLimitNotification' => $creditLimitNotification
        );
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
