<?php

namespace Rbs\Bundle\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\EventDispatcher\Event;

class BaseController extends Controller
{
    protected function dispatch($eventName, Event $event)
    {
        $this->get('event_dispatcher')->dispatch($eventName, $event);
    }

    protected function flashMessage($key, $message)
    {
        $this->get('session')->getFlashBag()->add($key, $message);
    }

    /**
     * @return \Rbs\Bundle\SalesBundle\Repository\CustomerRepository
     */
    protected function customerRepository()
    {
        return $this->getDoctrine()->getRepository('RbsSalesBundle:Customer');
    }

    /**
     * @return \Rbs\Bundle\SalesBundle\Repository\OrderRepository
     */
    protected function orderRepository()
    {
        return $this->getDoctrine()->getRepository('RbsSalesBundle:Order');
    }

    /**
     * @return \Rbs\Bundle\SalesBundle\Repository\PaymentRepository
     */
    protected function paymentRepository()
    {
        return $this->getDoctrine()->getRepository('RbsSalesBundle:Payment');
    }
}
