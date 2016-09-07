<?php

namespace Rbs\Bundle\SalesBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Rbs\Bundle\CoreBundle\Entity\Depo;
use Xiidea\EasyAuditBundle\Annotation\ORMSubscribedEvents;
use Knp\DoctrineBehaviors\Model as ORMBehaviors;

/**
 * Delivery
 *
 * @ORM\Table(name="sales_deliveries")
 * @ORM\Entity(repositoryClass="Rbs\Bundle\SalesBundle\Repository\DeliveryRepository")
 * @ORMSubscribedEvents()
 */
class Delivery
{
    const NOURISH = "NOURISH";
    const AGENT = "AGENT";
    
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
     * @ORM\ManyToOne(targetEntity="Rbs\Bundle\SalesBundle\Entity\Order", inversedBy="deliveries")
     * @ORM\JoinColumn(name="order_id", nullable=false)
     */
    private $orderRef;

    /**
     * @var Depo
     *
     * @ORM\ManyToOne(targetEntity="Rbs\Bundle\CoreBundle\Entity\Depo")
     * @ORM\JoinColumn(name="depo_id", nullable=false)
     */
    private $depo;

    /**
     * @var DeliveryItem
     *
     * @ORM\OneToMany(targetEntity="Rbs\Bundle\SalesBundle\Entity\DeliveryItem", mappedBy="delivery")
     **/
    private $deliveryItems;

    /**
     * @var TruckInfo
     *
     * @ORM\OneToMany(targetEntity="Rbs\Bundle\SalesBundle\Entity\TruckInfo", mappedBy="deliveries")
     */
    private $truckInfos;
    
    /**
     * @var array $type
     *
     * @ORM\Column(name="transport_given", type="string", length=255, columnDefinition="ENUM('NOURISH', 'AGENT')", nullable=false)
     */
    private $transportGiven = 'NOURISH';

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
     * Set depo
     *
     * @param string $depo
     * @return Delivery
     */
    public function setDepo($depo)
    {
        $this->depo = $depo;

        return $this;
    }

    /**
     * Get depo
     *
     * @return Depo
     */
    public function getDepo()
    {
        return $this->depo;
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

    /**
     * @return DeliveryItem
     */
    public function getDeliveryItems()
    {
        return $this->deliveryItems;
    }

    /**
     * @param DeliveryItem $deliveryItems
     *
     * @return Delivery
     */
    public function setDeliveryItems($deliveryItems)
    {
        $this->deliveryItems = $deliveryItems;

        return $this;
    }

    /**
     * @return array
     */
    public function getTransportGiven()
    {
        return $this->transportGiven;
    }

    /**
     * @param array $transportGiven
     */
    public function setTransportGiven($transportGiven)
    {
        $this->transportGiven = $transportGiven;
    }

    /**
     * @return TruckInfo
     */
    public function getTruckInfos()
    {
        return $this->truckInfos;
    }

    /**
     * @param TruckInfo $truckInfos
     */
    public function setTruckInfos($truckInfos)
    {
        $this->truckInfos = $truckInfos;
    }
}
