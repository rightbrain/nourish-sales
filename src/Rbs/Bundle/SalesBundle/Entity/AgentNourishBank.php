<?php
namespace Rbs\Bundle\SalesBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Rbs\Bundle\CoreBundle\Entity\BankAccount;
use Symfony\Component\Validator\Constraints as Assert;
use Knp\DoctrineBehaviors\Model as ORMBehaviors;
use Xiidea\EasyAuditBundle\Annotation\ORMSubscribedEvents;

/**
 * AgentNourishBank
 *
 * @ORM\Table(name="sales_agent_nourish_banks")
 * @ORM\Entity(repositoryClass="Rbs\Bundle\SalesBundle\Repository\AgentNourishBankRepository")
 */
class AgentNourishBank
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var Agent
     *
     * @ORM\ManyToOne(targetEntity="Rbs\Bundle\SalesBundle\Entity\Agent")
     * @ORM\JoinColumn(name="agent_id")
     */
    private $agent;

    /**
     * @var BankAccount
     *
     * @ORM\ManyToOne(targetEntity="Rbs\Bundle\CoreBundle\Entity\BankAccount", inversedBy="agentNourishBank")
     * @ORM\JoinColumn(name="nourish_account_id")
     */
    private $account;

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
     * @return Agent
     */
    public function getAgent()
    {
        return $this->agent;
    }

    /**
     * @param Agent $agent
     */
    public function setAgent($agent)
    {
        $this->agent = $agent;
    }

    /**
     * @return BankAccount
     */
    public function getAccount()
    {
        return $this->account;
    }

    /**
     * @param BankAccount $account
     */
    public function setAccount($account)
    {
        $this->account = $account;
    }
}