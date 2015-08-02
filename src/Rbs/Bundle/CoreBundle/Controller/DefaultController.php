<?php

namespace Rbs\Bundle\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class DefaultController extends Controller
{
    /**
     * @Route("/hello", name="homepage")
     * @Template()
     */
    public function indexAction()
    {
        return array();
    }

    /**
     * @Route("/hello2", name="homepage2")
     * @Template()
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
