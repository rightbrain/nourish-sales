<?php
namespace Rbs\Bundle\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Knp\DoctrineBehaviors\Model as ORMBehaviors;
use Xiidea\EasyAuditBundle\Annotation\ORMSubscribedEvents;

/**
 * SaleIncentive
 *
 * @ORM\Table(name="sale_incentives")
 * @ORM\Entity(repositoryClass="Rbs\Bundle\CoreBundle\Repository\SaleIncentiveRepository")
 * @ORMSubscribedEvents()
 */
class SaleIncentive
{
    const YEAR = 'YEAR';
    const MONTH = 'MONTH';
    
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
     * @var Category
     *
     * @ORM\ManyToOne(targetEntity="Rbs\Bundle\CoreBundle\Entity\Category")
     * @ORM\JoinColumn(name="category_id")
     */
    private $category;

    /**
     * @var array $type
     *
     * @ORM\Column(name="duration_type", type="string", length=255, columnDefinition="ENUM('MONTH', 'YEAR')", nullable=false)
     */
    private $durationType;

    /**
     * @var float
     *
     * @ORM\Column(name="amounts", type="float", nullable=false)
     */
    private $amount;

    /**
     * @var integer
     *
     * @ORM\Column(name="quantities", type="integer", nullable=false)
     */
    private $quantity;

    /**
     * @var string
     *
     * @ORM\Column(name="incentive_group", type="string", length=255, nullable=false)
     */
    private $incentiveGroup;
    
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
     * @return Category
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @param Category $category
     */
    public function setCategory($category)
    {
        $this->category = $category;
    }

    /**
     * @return array
     */
    public function getDurationType()
    {
        return $this->durationType;
    }

    /**
     * @param array $durationType
     */
    public function setDurationType($durationType)
    {
        $this->durationType = $durationType;
    }

    /**
     * @return float
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param float $amount
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
    }

    /**
     * @return int
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * @param int $quantity
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;
    }

    /**
     * @return string
     */
    public function getIncentiveGroup()
    {
        return $this->incentiveGroup;
    }

    /**
     * @param string $incentiveGroup
     */
    public function setIncentiveGroup($incentiveGroup)
    {
        $this->incentiveGroup = $incentiveGroup;
    }
}