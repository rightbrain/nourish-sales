<?php
namespace Rbs\Bundle\SalesBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Rbs\Bundle\CoreBundle\Entity\Depo;
use Rbs\Bundle\CoreBundle\Entity\ItemType;
use Rbs\Bundle\UserBundle\Entity\User;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Knp\DoctrineBehaviors\Model as ORMBehaviors;
use Xiidea\EasyAuditBundle\Annotation\ORMSubscribedEvents;

/**
 * Agent
 *
 * @ORM\Table(name="sales_agents")
 * @ORM\Entity(repositoryClass="Rbs\Bundle\SalesBundle\Repository\AgentRepository")
 * @ORMSubscribedEvents()
 * @UniqueEntity(fields={"agentCodeForDatatable", "agentType"} , message="Agent Id is unique.")
 */
class Agent
{
    const DR = 'DR';
    const CR = 'CR';

    const AGENT_TYPE_FEED = 'FEED';
    const AGENT_TYPE_CHICK = 'CHICK';

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
     * @ORM\OneToOne(targetEntity="Rbs\Bundle\UserBundle\Entity\User", inversedBy="agent", cascade={"persist"})
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
     * @ORM\OneToMany(targetEntity="AgentDoc", mappedBy="agent", cascade={"persist"})
     */
    private $agentDocs;

    /**
     * @ORM\OneToMany(targetEntity="AgentsBankInfo", mappedBy="agent", cascade={"persist"})
     */
    private $agentsBankInfo;

    /**
     * @var float
     *
     * @ORM\Column(name="opening_balance", type="float", nullable=true)
     */
    private $openingBalance = 0;

    /**
     * @var array $type
     *
     * @ORM\Column(name="opening_balance_type", type="string", length=255, columnDefinition="ENUM('DR', 'CR')", nullable=true)
     */
    private $openingBalanceType;

    /**
     * @var boolean
     *
     * @ORM\Column(name="opening_balance_flag", type="boolean", nullable=true)
     */
    private $openingBalanceFlag = false;
    
    /**
     * @var string
     *
     * @ORM\Column(name="agent_ID", type="string", length=255, nullable=true)
     */
    private $agentID;

    /**
     * @var string
     *
     * @ORM\Column(name="chick_agent_id", type="string", length=255, nullable=true)
     */
    private $chickAgentID;

    /**
     * @var string
     * @ORM\Column(name="agent_code_for_datatable", type="string", length=255, nullable=true)
     *
     */
    private $agentCodeForDatatable;

    /**
     * @var ItemType
     *
     * @ORM\ManyToOne(targetEntity="Rbs\Bundle\CoreBundle\Entity\ItemType")
     * @ORM\JoinColumn(name="item_type", nullable=true)
     */
    private $itemType;

    /**
     * @var ItemType
     *
     * @ORM\ManyToMany(targetEntity="Rbs\Bundle\CoreBundle\Entity\ItemType", inversedBy="agent")
     * @ORM\JoinTable(name="sales_join_agents_item_types")
     */
    private $itemTypes;

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
     *
     */
    private $depo;

    /**
     * @var Depo
     *
     * @ORM\ManyToOne(targetEntity="Rbs\Bundle\CoreBundle\Entity\Depo")
     * @ORM\JoinColumn(name="depot_for_chick", nullable=true)
     *
     */
    private $depotForChick;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Rbs\Bundle\SalesBundle\Entity\ChickenSetForAgent", mappedBy="agent")
     */
    private $chickenSetForAgent;

    /**
     * @var array $type
     *
     * @ORM\Column(name="agent_type", type="string", length=255, columnDefinition="ENUM('FEED', 'CHICK')")
     */
    private $agentType=self::AGENT_TYPE_FEED;

    /**
     * @var string
     *
     * @ORM\Column(name="delivery_point", type="text", nullable=true)
     */
    private $deliveryPoint;


    public function __construct()
    {
        $this->itemTypes = new ArrayCollection();
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

    /**
     * @return mixed
     */
    public function getAgentDocs()
    {
        return $this->agentDocs;
    }

    /**
     * @param mixed $agentDocs
     */
    public function setAgentDocs($agentDocs)
    {
        $this->agentDocs = $agentDocs;
    }

    /**
     * @return mixed
     */
    public function getAgentsBankInfo()
    {
        return $this->agentsBankInfo;
    }

    /**
     * @param mixed $agentsBankInfo
     */
    public function setAgentsBankInfo($agentsBankInfo)
    {
        $this->agentsBankInfo = $agentsBankInfo;
    }

    /**
     * @return array
     */
    public function getOpeningBalanceType()
    {
        return $this->openingBalanceType;
    }

    /**
     * @param array $openingBalanceType
     */
    public function setOpeningBalanceType($openingBalanceType)
    {
        $this->openingBalanceType = $openingBalanceType;
    }

    /**
     * @return string
     */
    public function getChickAgentID()
    {
        return $this->chickAgentID;
    }

    /**
     * @param string $chickAgentID
     */
    public function setChickAgentID($chickAgentID)
    {
        $this->chickAgentID = $chickAgentID;
    }

    /**
     * @return Depo
     */
    public function getDepotForChick()
    {
        return $this->depotForChick;
    }

    /**
     * @param Depo $depotForChick
     */
    public function setDepotForChick($depotForChick)
    {
        $this->depotForChick = $depotForChick;
    }

    /**
     * @return boolean
     */
    public function isOpeningBalanceFlag()
    {
        return $this->openingBalanceFlag;
    }

    /**
     * @param boolean $openingBalanceFlag
     */
    public function setOpeningBalanceFlag($openingBalanceFlag)
    {
        $this->openingBalanceFlag = $openingBalanceFlag;
    }

    /**
     * @return ArrayCollection
     */
    public function getChickenSetForAgent()
    {
        return $this->chickenSetForAgent;
    }

    /**
     * @param ArrayCollection $chickenSetForAgent
     */
    public function setChickenSetForAgent($chickenSetForAgent)
    {
        $this->chickenSetForAgent = $chickenSetForAgent;
    }

    /**
     * @return array
     */
    public function getAgentType()
    {
        return $this->agentType;
    }

    /**
     * @param array $agentType
     */
    public function setAgentType($agentType)
    {
        $this->agentType = $agentType;
    }

    /**
     * @return string
     */
    public function getAgentCodeForDatatable()
    {
        return $this->agentCodeForDatatable;
    }

    /**
     * @param string $agentCodeForDatatable
     */
    public function setAgentCodeForDatatable($agentCodeForDatatable)
    {
        $this->agentCodeForDatatable = $agentCodeForDatatable;
    }


    public function getName()
    {
        return !empty($this->getUser()->getProfile()->getFullName()) 
            ? $this->getUser()->getProfile()->getFullName()
            : $this->getUser()->getUsername();
    }

    public static function agentIdNameFormat($id, $name)
    {
        return $id . ' - ' . $name;
    }

    public function getIdName()
    {
        if($this->agentType == Agent::AGENT_TYPE_CHICK){
            return Agent::agentIdNameFormat($this->getChickAgentID(), $this->getUser()->getProfile()->getFullName());
        }else{
            return Agent::agentIdNameFormat($this->getAgentID(), $this->getUser()->getProfile()->getFullName());
        }
    }
    public function getIdNameForChick()
    {
        return Agent::agentIdNameFormat($this->getChickAgentID(), $this->getUser()->getProfile()->getFullName());
    }

    /**
     * @return ItemType
     */
    public function getItemTypes()
    {
        return $this->itemTypes;
    }

    /**
     * @param ItemType $itemTypes
     */
    public function setItemTypes($itemTypes)
    {
        $this->itemTypes = $itemTypes;
    }

    /**
     * @return string
     */
    public function getDeliveryPoint()
    {
        return $this->deliveryPoint;
    }

    /**
     * @param string $deliveryPoint
     */
    public function setDeliveryPoint($deliveryPoint)
    {
        $this->deliveryPoint = $deliveryPoint;
    }




}