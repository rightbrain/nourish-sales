<?php
namespace Rbs\Bundle\SalesBundle\Entity;

use Rbs\Bundle\CoreBundle\Entity\Area;
use Rbs\Bundle\CoreBundle\Entity\Warehouse;
use Rbs\Bundle\UserBundle\Entity\User;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Knp\DoctrineBehaviors\Model as ORMBehaviors;
use Xiidea\EasyAuditBundle\Annotation\ORMSubscribedEvents;

/**
 * Customer
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
     * @var CustomerGroup
     *
     * @ORM\ManyToOne(targetEntity="Rbs\Bundle\SalesBundle\Entity\CustomerGroup", inversedBy="customers")
     * @ORM\JoinColumn(name="customer_group")
     */
    private $customerGroup;

    /**
     * @ORM\OneToMany(targetEntity="Payment", mappedBy="customer", cascade={"persist"})
     */
    private $payments;

    /**
     * @var float
     *
     * @ORM\Column(name="credit_limit", type="float", nullable=true)
     */
    private $creditLimit = 0;

    /**
     * @var float
     *
     * @ORM\Column(name="opening_balance", type="float", nullable=true)
     */
    private $openingBalance = 0;

    /**
     * @var string
     *
     * @ORM\Column(name="customer_ID", type="string", length=255, nullable=false)
     */
    private $customerID;

    /**
     * @var boolean
     *
     * @ORM\Column(name="vip", type="boolean", nullable=true)
     */
    private $VIP;

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

    /**
     * @return CustomerGroup
     */
    public function getCustomerGroup()
    {
        return $this->customerGroup;
    }

    /**
     * @param CustomerGroup $customerGroup
     */
    public function setCustomerGroup($customerGroup)
    {
        $this->customerGroup = $customerGroup;
    }

    /**
     * @return boolean
     */
    public function isVIP()
    {
        return $this->VIP;
    }

    /**
     * @param boolean $VIP
     */
    public function setVIP($VIP)
    {
        $this->VIP = $VIP;
    }

    /**
     * @return float
     */
    public function getOpeningBalance()
    {
        return $this->openingBalance;
    }

    /**
     * @param float $openingBalance
     */
    public function setOpeningBalance($openingBalance)
    {
        $this->openingBalance = $openingBalance;
    }

    /**
     * @return mixed
     */
    public function getPayments()
    {
        return $this->payments;
    }

    /**
     * @param mixed $payments
     */
    public function setPayments($payments)
    {
        $this->payments = $payments;
    }

    protected function getCurrentCreditLimit()
    {
        // (order(processing+complete) total amount - payment total) - (credit limit + opening balance)
        return false;
    }

    protected function getCurrentBalance()
    {
        // order(processing+complete) total amount - payment total
        return false;
    }
}