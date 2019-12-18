<?php
namespace Rbs\Bundle\SalesBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Rbs\Bundle\CoreBundle\Entity\Depo;
use Rbs\Bundle\UserBundle\Entity\User;
use Symfony\Component\Validator\Constraints as Assert;
use Knp\DoctrineBehaviors\Model as ORMBehaviors;
use Xiidea\EasyAuditBundle\Annotation\ORMSubscribedEvents;

/**
 * VehicleNourish
 *
 * @ORM\Table(name="sales_vehicles_nourish")
 * @ORM\Entity(repositoryClass="Rbs\Bundle\SalesBundle\Repository\VehicleNourishRepository")
 * @ORMSubscribedEvents()
 * @ORM\HasLifecycleCallbacks
 */
class VehicleNourish
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
     * @ORM\Column(name="driver_name", type="text", nullable=true)
     */
    private $driverName;

    /**
     * @var string
     *
     * @ORM\Column(name="driver_phone", type="text", nullable=true)
     */
    private $driverPhone;

    /**
     * @var string
     *
     * @ORM\Column(name="truck_number", type="text", nullable=true)
     */
    private $truckNumber;

    
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
     * @var Depo
     *
     * @ORM\ManyToOne(targetEntity="Rbs\Bundle\CoreBundle\Entity\Depo")
     * @ORM\JoinColumn(name="depo_id", nullable=true)
     */
    private $depo;

    /**
     * @return string
     */
    public function getDriverName()
    {
        return $this->driverName;
    }

    /**
     * @param string $driverName
     */
    public function setDriverName($driverName)
    {
        $this->driverName = $driverName;
    }

    /**
     * @return string
     */
    public function getDriverPhone()
    {
        return $this->driverPhone;
    }

    /**
     * @param string $driverPhone
     */
    public function setDriverPhone($driverPhone)
    {
        $this->driverPhone = $driverPhone;
    }

    /**
     * @return string
     */
    public function getTruckNumber()
    {
        return $this->truckNumber;
    }

    /**
     * @param string $truckNumber
     */
    public function setTruckNumber($truckNumber)
    {
        $this->truckNumber = $truckNumber;
    }

    /**
     * @return Depo
     */
    public function getDepo()
    {
        return $this->depo;
    }

    /**
     * @param Depo $depo
     */
    public function setDepo($depo)
    {
        $this->depo = $depo;
    }


    public function getTruckInformation()
    {
        return '#' . ' Truck No. :'. $this->getTruckNumber() . ',' . ' Driver Name :' . $this->getDriverName() . ',' . ' Mobile :' . $this->getDriverPhone();
    }

}