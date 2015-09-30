<?php

namespace Rbs\Bundle\SalesBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Xiidea\EasyAuditBundle\Annotation\ORMSubscribedEvents;
use Knp\DoctrineBehaviors\Model as ORMBehaviors;

/**
 * Delivery
 *
 * @ORM\Table(name="deliveries")
 * @ORM\Entity(repositoryClass="Rbs\Bundle\SalesBundle\Repository\DeliveryRepository")
 * @ORMSubscribedEvents()
 */
class Delivery
{
    use ORMBehaviors\Timestampable\Timestampable,
        ORMBehaviors\SoftDeletable\SoftDeletable,
        ORMBehaviors\Blameable\Blameable;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="contactName", type="string", length=255, nullable=true)
     */
    private $contactName;

    /**
     * @var string
     *
     * @ORM\Column(name="contactNo", type="string", length=255, nullable=true)
     */
    private $contactNo;

    /**
     * @var string
     *
     * @ORM\Column(name="otherInfo", type="text", nullable=true)
     */
    private $otherInfo;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="vehicleIn", type="datetime", nullable=true)
     */
    private $vehicleIn;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="vehicleOut", type="datetime", nullable=true)
     */
    private $vehicleOut;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="startLoad", type="datetime", nullable=true)
     */
    private $startLoad;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="finishLoad", type="datetime", nullable=true)
     */
    private $finishLoad;

    /**
     * @var Order
     *
     * @ORM\ManyToOne(targetEntity="Rbs\Bundle\SalesBundle\Entity\Order")
     * @ORM\JoinColumn(name="order_id", nullable=false)
     */
    private $orderRef;

    /**
     * @var Order
     *
     * @ORM\ManyToOne(targetEntity="Rbs\Bundle\CoreBundle\Entity\Warehouse")
     * @ORM\JoinColumn(name="warehouse_id", nullable=false)
     */
    private $warehouse;

    /**
     * @var DeliveryItem
     *
     * @ORM\ManyToMany(targetEntity="Rbs\Bundle\SalesBundle\Entity\DeliveryItem", mappedBy="delivery")
     **/
    private $deliveryItems;

    public function __construct()
    {
        $this->deliveryItems = new ArrayCollection();
    }

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set vehicleIn
     *
     * @param \DateTime $vehicleIn
     * @return Delivery
     */
    public function setVehicleIn($vehicleIn)
    {
        $this->vehicleIn = $vehicleIn;

        return $this;
    }

    /**
     * Get vehicleIn
     *
     * @return \DateTime 
     */
    public function getVehicleIn()
    {
        return $this->vehicleIn;
    }

    /**
     * Set vehicleOut
     *
     * @param \DateTime $vehicleOut
     * @return Delivery
     */
    public function setVehicleOut($vehicleOut)
    {
        $this->vehicleOut = $vehicleOut;

        return $this;
    }

    /**
     * Get vehicleOut
     *
     * @return \DateTime 
     */
    public function getVehicleOut()
    {
        return $this->vehicleOut;
    }

    /**
     * Set startLoad
     *
     * @param \DateTime $startLoad
     * @return Delivery
     */
    public function setStartLoad($startLoad)
    {
        $this->startLoad = $startLoad;

        return $this;
    }

    /**
     * Get startLoad
     *
     * @return \DateTime 
     */
    public function getStartLoad()
    {
        return $this->startLoad;
    }

    /**
     * Set finishLoad
     *
     * @param \DateTime $finishLoad
     * @return Delivery
     */
    public function setFinishLoad($finishLoad)
    {
        $this->finishLoad = $finishLoad;

        return $this;
    }

    /**
     * Get finishLoad
     *
     * @return \DateTime 
     */
    public function getFinishLoad()
    {
        return $this->finishLoad;
    }

    /**
     * Set contactName
     *
     * @param string $contactName
     * @return Delivery
     */
    public function setContactName($contactName)
    {
        $this->contactName = $contactName;

        return $this;
    }

    /**
     * Get contactName
     *
     * @return string 
     */
    public function getContactName()
    {
        return $this->contactName;
    }

    /**
     * Set contactNo
     *
     * @param string $contactNo
     * @return Delivery
     */
    public function setContactNo($contactNo)
    {
        $this->contactNo = $contactNo;

        return $this;
    }

    /**
     * Get contactNo
     *
     * @return string 
     */
    public function getContactNo()
    {
        return $this->contactNo;
    }

    /**
     * Set otherInfo
     *
     * @param string $otherInfo
     * @return Delivery
     */
    public function setOtherInfo($otherInfo)
    {
        $this->otherInfo = $otherInfo;

        return $this;
    }

    /**
     * Get otherInfo
     *
     * @return string 
     */
    public function getOtherInfo()
    {
        return $this->otherInfo;
    }

    /**
     * Set warehouse
     *
     * @param string $warehouse
     * @return Delivery
     */
    public function setWarehouse($warehouse)
    {
        $this->warehouse = $warehouse;

        return $this;
    }

    /**
     * Get warehouse
     *
     * @return string 
     */
    public function getWarehouse()
    {
        return $this->warehouse;
    }

    /**
     * @return Order
     */
    public function getOrderRef()
    {
        return $this->orderRef;
    }

    /**
     * @param Order $order
     *
     * @return Delivery
     */
    public function setOrderRef($order)
    {
        $this->orderRef = $order;

        return $this;
    }

}
