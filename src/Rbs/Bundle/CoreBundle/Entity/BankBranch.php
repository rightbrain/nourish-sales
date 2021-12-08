<?php

namespace Rbs\Bundle\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * BankBranch
 *
 * @ORM\Table(name="core_bank_branches")
 * @ORM\Entity(repositoryClass="Rbs\Bundle\CoreBundle\Repository\BankBranchRepository")
 */
class BankBranch
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
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var Bank
     *
     * @ORM\ManyToOne(targetEntity="Rbs\Bundle\CoreBundle\Entity\Bank", inversedBy="branches")
     * @ORM\JoinColumn(name="bank_id")
     */
    private $bank;

    /**
     * @ORM\Column(name="mobile", type="string", nullable=true)
     */
    private $mobile;

    /**
     * @ORM\Column(name="branch_code", type="string", nullable=true)
     */
    private $branchCode;

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
     * Set name
     *
     * @param string $name
     * @return BankBranch
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set bank
     *
     * @param Bank $bank
     * @return BankBranch
     */
    public function setBank($bank)
    {
        $this->bank = $bank;

        return $this;
    }

    /**
     * Get bank
     *
     * @return Bank
     */
    public function getBank()
    {
        return $this->bank;
    }

    public function nameWithBank()
    {
        return $this->bank->getName() . ' - ' . $this->name;
    }


    public function nameWithCode()
    {
        return '('.$this->branchCode.') ' . $this->name;
    }


    public function nameWithCodeBank()
    {
        return '('.$this->branchCode.') ' . $this->name.' - '.$this->bank->getName();
    }

    /**
     * @return mixed
     */
    public function getMobile()
    {
        return $this->mobile;
    }

    /**
     * @param mixed $mobile
     */
    public function setMobile($mobile)
    {
        $this->mobile = $mobile;
    }

    /**
     * @return mixed
     */
    public function getBranchCode()
    {
        return $this->branchCode;
    }

    /**
     * @param mixed $branchCode
     */
    public function setBranchCode($branchCode)
    {
        $this->branchCode = $branchCode;
    }

}
