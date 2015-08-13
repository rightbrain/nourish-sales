<?php
namespace Rbs\Bundle\SalesBundle\Entity;

use Rbs\Bundle\CoreBundle\Entity\Area;
use Rbs\Bundle\CoreBundle\Entity\Bundle;
use Rbs\Bundle\CoreBundle\Entity\Warehouse;
use Rbs\Bundle\UserBundle\Entity\User;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Knp\DoctrineBehaviors\Model as ORMBehaviors;
use Xiidea\EasyAuditBundle\Annotation\ORMSubscribedEvents;

/**
 * Item
 *
 * @ORM\Table(name="customers")
 * @ORM\Entity(repositoryClass="Rbs\Bundle\SalesBundle\Repository\CustomerRepository")
 * @ORMSubscribedEvents()
 */
class Customer
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
     * @var User
     *
     * @ORM\OneToOne(targetEntity="Rbs\Bundle\UserBundle\Entity\User", cascade={"persist"})
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="user_id", referencedColumnName="id", unique=true, onDelete="CASCADE")
     * })
     */
    protected $user;

    /**
     * @var float
     *
     * @ORM\Column(name="credit_limit", type="float", nullable=true)
     */
    private $creditLimit;

    /**
     * @var float
     *
     * @ORM\Column(name="balance", type="float", nullable=true)
     */
    private $balance;

    /**
     * @var string
     *
     * @ORM\Column(name="customer_ID", type="string", length=255, nullable=true)
     */
    private $customerID;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="Rbs\Bundle\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="agent", nullable=true)
     */
    private $agent;

    /**
     * @var Warehouse
     *
     * @ORM\ManyToOne(targetEntity="Rbs\Bundle\CoreBundle\Entity\Warehouse")
     * @ORM\JoinColumn(name="warehouse", nullable=true)
     */
    private $warehouse;

    /**
     * @var Area
     *
     * @ORM\ManyToOne(targetEntity="Rbs\Bundle\CoreBundle\Entity\Area")
     * @ORM\JoinColumn(name="area", nullable=true)
     */
    private $area;

    /**
     * @ORM\ManyToMany(targetEntity="Rbs\Bundle\CoreBundle\Entity\Bundle")
     **/
    private $bundles;

    public function __construct()
    {
        $this->bundles = new ArrayCollection();
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

    public function __toString()
    {
        return (string)$this->getId();
    }

    /**
     * Add bundle
     *
     * @param Bundle $bundle
     * @return $this
     */
    public function addBundle(Bundle $bundle)
    {
        if (!$this->getBundles()->contains($bundle)) {
            $this->bundles->add($bundle);
        }

        return $this;
    }

    /**
     * Remove bundle
     *
     * @param Bundle $bundle
     */
    public function removeBundle(Bundle $bundle)
    {
        $this->bundles->removeElement($bundle);
    }

    /**
     * Get bundle
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getBundles()
    {
        return $this->bundles;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param User $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * @return float
     */
    public function getCreditLimit()
    {
        return $this->creditLimit;
    }

    /**
     * @param float $creditLimit
     */
    public function setCreditLimit($creditLimit)
    {
        $this->creditLimit = $creditLimit;
    }

    /**
     * @return float
     */
    public function getBalance()
    {
        return $this->balance;
    }

    /**
     * @param float $balance
     */
    public function setBalance($balance)
    {
        $this->balance = $balance;
    }

    /**
     * @return string
     */
    public function getCustomerID()
    {
        return $this->customerID;
    }

    /**
     * @param string $customerID
     */
    public function setCustomerID($customerID)
    {
        $this->customerID = $customerID;
    }

    /**
     * @return User
     */
    public function getAgent()
    {
        return $this->agent;
    }

    /**
     * @param User $agent
     */
    public function setAgent($agent)
    {
        $this->agent = $agent;
    }

    /**
     * @return Warehouse
     */
    public function getWarehouse()
    {
        return $this->warehouse;
    }

    /**
     * @param Warehouse $warehouse
     */
    public function setWarehouse($warehouse)
    {
        $this->warehouse = $warehouse;
    }

    /**
     * @return Area
     */
    public function getArea()
    {
        return $this->area;
    }

    /**
     * @param Area $area
     */
    public function setArea($area)
    {
        $this->area = $area;
    }
}