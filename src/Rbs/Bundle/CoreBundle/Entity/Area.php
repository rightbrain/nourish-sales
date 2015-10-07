<?php
namespace Rbs\Bundle\CoreBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Model as ORMBehaviors;
use Xiidea\EasyAuditBundle\Annotation\ORMSubscribedEvents;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Area
 *
 * @ORM\Table(name="areas")
 * @ORM\Entity(repositoryClass="Rbs\Bundle\CoreBundle\Repository\AreaRepository")
 * @ORMSubscribedEvents()
 */
class Area
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
     * @ORM\Column(name="areas_name", type="string", length=255)
     * @Assert\NotBlank()
     */
    private $areaName;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Rbs\Bundle\CoreBundle\Entity\Vendor", mappedBy="area", cascade={"persist"})
     *
     */
    protected $vendor;

    /**
     * @var integer
     *
     * @ORM\Column(name="status", type="integer")
     */
    private $status = 1;

    /**
     * @var \Rbs\Bundle\CoreBundle\Entity\Address
     *
     * @ORM\ManyToOne(targetEntity="Rbs\Bundle\CoreBundle\Entity\Address")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="level_1", referencedColumnName="c1")
     * })
     */
    private $level1;

    /**
     * @var \Rbs\Bundle\CoreBundle\Entity\Address
     *
     * @ORM\ManyToOne(targetEntity="Rbs\Bundle\CoreBundle\Entity\Address")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="level_2", referencedColumnName="c1", nullable=true)
     * })
     */
    private $level2;

    /**
     * @var \Rbs\Bundle\CoreBundle\Entity\Address
     *
     * @ORM\ManyToOne(targetEntity="Rbs\Bundle\CoreBundle\Entity\Address")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="level_3", referencedColumnName="c1", nullable=true)
     * })
     */
    private $level3;

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
        return $this->getAreaName();
    }

    /**
     * Set areaName
     *
     * @param string $areaName
     * @return Area
     */
    public function setAreaName($areaName)
    {
        $this->areaName = $areaName;

        return $this;
    }

    /**
     * Get areaName
     *
     * @return string
     */
    public function getAreaName()
    {
        return $this->areaName;
    }

    /**
     * Set status
     *
     * @param integer $status
     * @return Area
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return integer
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @return ArrayCollection
     */
    public function getVendor()
    {
        return $this->vendor;
    }

    /**
     * @param ArrayCollection $vendor
     */
    public function setVendor($vendor)
    {
        $this->vendor = $vendor;
    }

    /**
     * Set level1
     *
     * @param \Rbs\Bundle\CoreBundle\Entity\Address $level1
     * @return $this
     */
    public function setLevel1(\Rbs\Bundle\CoreBundle\Entity\Address $level1 = null)
    {
        $this->level1 = $level1;

        return $this;
    }

    /**
     * Get level1
     *
     * @return \Rbs\Bundle\CoreBundle\Entity\Address
     */
    public function getLevel1()
    {
        return $this->level1;
    }

    /**
     * Set level2
     *
     * @param \Rbs\Bundle\CoreBundle\Entity\Address $level2
     * @return $this
     */
    public function setLevel2(\Rbs\Bundle\CoreBundle\Entity\Address $level2 = null)
    {
        $this->level2 = $level2;

        return $this;
    }

    /**
     * Get level2
     *
     * @return \Rbs\Bundle\CoreBundle\Entity\Address
     */
    public function getLevel2()
    {
        return $this->level2;
    }

    /**
     * Set level3
     *
     * @param \Rbs\Bundle\CoreBundle\Entity\Address $level3
     * @return $this
     */
    public function setLevel3(\Rbs\Bundle\CoreBundle\Entity\Address $level3 = null)
    {
        $this->level3 = $level3;

        return $this;
    }

    /**
     * Get level3
     *
     * @return \Rbs\Bundle\CoreBundle\Entity\Address
     */
    public function getLevel3()
    {
        return $this->level3;
    }
}