<?php
namespace Rbs\Bundle\SalesBundle\Entity;

use Rbs\Bundle\CoreBundle\Entity\Depo;
use Rbs\Bundle\CoreBundle\Entity\ItemType;
use Rbs\Bundle\CoreBundle\Entity\Location;
use Rbs\Bundle\UserBundle\Entity\User;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Knp\DoctrineBehaviors\Model as ORMBehaviors;
use Xiidea\EasyAuditBundle\Annotation\ORMSubscribedEvents;

/**
 * Agent
 *
 * @ORM\Table(name="agents")
 * @ORM\Entity(repositoryClass="Rbs\Bundle\SalesBundle\Repository\AgentRepository")
 * @ORMSubscribedEvents()
 * @UniqueEntity("agentID")
 */
class Agent
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
     * @var AgentGroup
     *
     * @ORM\ManyToOne(targetEntity="Rbs\Bundle\SalesBundle\Entity\AgentGroup", inversedBy="agents")
     * @ORM\JoinColumn(name="agent_group")
     */
    private $agentGroup;

    /**
     * @ORM\OneToMany(targetEntity="Payment", mappedBy="agent", cascade={"persist"})
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
     * @ORM\Column(name="agent_ID", type="string", length=255, nullable=false, unique=true)
     * @Assert\NotBlank()
     */
    private $agentID;

    /**
     * @var ItemType
     *
     * @ORM\ManyToOne(targetEntity="Rbs\Bundle\CoreBundle\Entity\ItemType")
     * @ORM\JoinColumn(name="item_type", nullable=true)
     */
    private $itemType;

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
     * @ORM\JoinColumn(name="sr", nullable=true)
     */
    private $sr;

    /**
     * @var Depo
     *
     * @ORM\ManyToOne(targetEntity="Rbs\Bundle\CoreBundle\Entity\Depo")
     * @ORM\JoinColumn(name="depo", nullable=true)
     * @Assert\NotBlank()
     */
    private $depo;

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
    public function getAgentID()
    {
        return $this->agentID;
    }

    /**
     * @param string $agentID
     */
    public function setAgentID($agentID)
    {
        $this->agentID = $agentID;
    }

    /**
     * @return User
     */
    public function getSr()
    {
        return $this->sr;
    }

    /**
     * @param User $sr
     */
    public function setSr($sr)
    {
        $this->sr = $sr;
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

    /**
     * @return AgentGroup
     */
    public function getAgentGroup()
    {
        return $this->agentGroup;
    }

    /**
     * @param AgentGroup $agentGroup
     */
    public function setAgentGroup($agentGroup)
    {
        $this->agentGroup = $agentGroup;
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
     * @return ItemType
     */
    public function getItemType()
    {
        return $this->itemType;
    }

    /**
     * @param ItemType $itemType
     */
    public function setItemType($itemType)
    {
        $this->itemType = $itemType;
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
        // (payment total + order(processing+complete) total amount) + (credit limit + opening balance)
        return false;
    }

    protected function getCurrentBalance()
    {
        // payment total - order(processing+complete) total amount
        return false;
    }
}