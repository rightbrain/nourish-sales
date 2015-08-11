<?php

namespace Rbs\Bundle\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Model as ORMBehaviors;
use Xiidea\EasyAuditBundle\Annotation\ORMSubscribedEvents;

/**
 * CostHeader
 *
 * @ORM\Table(name="cost_headers")
 * @ORM\Entity(repositoryClass="Rbs\Bundle\CoreBundle\Repository\CostHeaderRepository")
 * @ORMSubscribedEvents()
 */
class CostHeader
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
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="titles", type="string", length=255)
     */
    private $title;

    /**
     * @var SubCategory
     *
     * @ORM\ManyToOne(targetEntity="Rbs\Bundle\CoreBundle\Entity\SubCategory", inversedBy="costHeader")
     * @ORM\JoinColumn(name="sub_categories", nullable=true)
     */
    private $subCategory;

    /**
     * @var integer
     *
     * @ORM\Column(name="status", type="integer")
     */
    private $status = 1;

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
     * Set subCategory
     *
     * @param SubCategory $subCategory
     * @return CostHeader
     */
    public function setSubCategory($subCategory)
    {
        $this->subCategory = $subCategory;

        return $this;
    }

    /**
     * Get subCategory
     *
     * @return SubCategory
     */
    public function getSubCategory()
    {
        return $this->subCategory;
    }

    /**
     * Set title
     *
     * @param string $title
     * @return CostHeader
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set status
     *
     * @param integer $status
     * @return CostHeader
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
}