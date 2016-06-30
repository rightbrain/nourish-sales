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
    const SALE = 'SALE';
    const TRANSPORT = 'TRANSPORT';
    
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
     * @ORM\Column(name="groups", type="string", length=255, nullable=false)
     */
    private $group;

    /**
     * @var array $type
     *
     * @ORM\Column(name="type", type="string", length=255, columnDefinition="ENUM('SALE', 'TRANSPORT')", nullable=false)
     */
    private $type;
    
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
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * @param string $group
     */
    public function setGroup($group)
    {
        $this->group = $group;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }
}