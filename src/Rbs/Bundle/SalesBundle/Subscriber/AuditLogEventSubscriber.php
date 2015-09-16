<?php

namespace Rbs\Bundle\SalesBundle\Subscriber;

use Xiidea\EasyAuditBundle\Subscriber\EasyAuditEventSubscriberInterface;

class AuditLogEventSubscriber implements EasyAuditEventSubscriberInterface
{
    public function getSubscribedEvents()
    {
        return array(
            "order.approved",
            "order.canceled",
            "order.hold",
            "order.completed",
        );
    }
}