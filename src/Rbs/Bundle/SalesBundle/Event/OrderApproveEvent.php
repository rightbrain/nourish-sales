<?php
namespace Rbs\Bundle\SalesBundle\Event;

use Rbs\Bundle\CoreBundle\Event\BaseEvent;
use Rbs\Bundle\SalesBundle\Entity\Order;

class OrderApproveEvent extends BaseEvent
{
    /**
     * @var Order
     */
    protected $order;

    /**
     * @var String
     */
    protected $eventName;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    /**
     * @param $eventName
     *
     * @return array
     */
    public function getEventLogInfo($eventName)
    {
        $this->eventName = $eventName;

        $eventType = $this->getEventType();
        $eventDescription = $this->getDescriptionString();

        return array(
            'description' => $eventDescription,
            'type' => $eventType,
        );
    }

    /**
     * @param string $typeName
     * @return string
     */
    protected function getEventShortName()
    {
        return substr(strrchr($this->eventName, '.'), 1);
    }

    protected function getEventType()
    {
        return ucwords(str_replace('.', ' ', $this->eventName));
    }

    protected function getDescriptionString()
    {
        $descriptionTemplate = "Order #%s has been %s";

        return sprintf($descriptionTemplate,
            $this->order->getId(),
            $this->getEventShortName()
        );
    }
}