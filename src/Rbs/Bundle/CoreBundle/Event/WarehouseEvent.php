<?php
namespace Rbs\Bundle\CoreBundle\Event;

use Rbs\Bundle\CoreBundle\Entity\Warehouse;
use Symfony\Component\EventDispatcher\Event;

class WarehouseEvent extends Event
{
    protected $warehouse;

    public function __construct(Warehouse $warehouse)
    {
        $this->warehouse = $warehouse;
    }

    public function getWarehouse()
    {
        return $this->warehouse;
    }
}