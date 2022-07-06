<?php
namespace Rbs\Bundle\SalesBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Knp\DoctrineBehaviors\Model as ORMBehaviors;

/**
 * DeliveryPoint
 *
 * @ORM\Table(name="sales_delivery_points")
 * @ORM\Entity(repositoryClass="Rbs\Bundle\SalesBundle\Repository\DeliveryPointRepository")
 */
class DeliveryPoint
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
     * @ORM\Column(name="point_address", type="text", nullable=true)
     */
    private $pointAddress;

    /**
     * @var string
     *
     * @ORM\Column(name="point_phone", type="string", nullable=true)
     */
    private $pointPhone;

    /**
     * @var string
     *
     * @ORM\Column(name="contact_person", type="string", nullable=true)
     */
    private $contactPerson;

    /**
     * @var boolean
     *
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $status = true;
    
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
     * @return string
     */
    public function getPointAddress()
    {
        return $this->pointAddress;
    }

    /**
     * @param string $pointAddress
     */
    public function setPointAddress($pointAddress)
    {
        $this->pointAddress = $pointAddress;
    }

    /**
     * @return string
     */
    public function getPointPhone()
    {
        return $this->pointPhone;
    }

    /**
     * @param string $pointPhone
     */
    public function setPointPhone($pointPhone)
    {
        $this->pointPhone = $pointPhone;
    }

    /**
     * @return string
     */
    public function getContactPerson()
    {
        return $this->contactPerson;
    }

    /**
     * @param string $contactPerson
     */
    public function setContactPerson($contactPerson)
    {
        $this->contactPerson = $contactPerson;
    }

    /**
     * @return bool
     */
    public function isStatus()
    {
        return $this->status;
    }

    /**
     * @param bool $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }


    public function isActive()
    {
        if($this->status){
            return 'Active';
        }
        return 'Inactive';
    }

}