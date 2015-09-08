<?php
namespace Rbs\Bundle\SalesBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Rbs\Bundle\CoreBundle\Entity\Project;
use Rbs\Bundle\CoreBundle\Entity\Warehouse;
use Symfony\Component\Validator\Constraints as Assert;
use Knp\DoctrineBehaviors\Model as ORMBehaviors;
use Xiidea\EasyAuditBundle\Annotation\ORMSubscribedEvents;

/**
 * Stock
 *
 * @ORM\Table(name="stock_histories")
 * @ORM\Entity(repositoryClass="Rbs\Bundle\SalesBundle\Repository\StockHistoryRepository")
 * @ORMSubscribedEvents()
 */
class StockHistory
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
     * @var integer
     *
     * @ORM\Column(name="quantity", type="integer", nullable=true)
     */
    private $quantity;

    /**
     * @var Stock
     *
     * @ORM\ManyToOne(targetEntity="Rbs\Bundle\SalesBundle\Entity\Stock", inversedBy="stockHistories")
     * @ORM\JoinColumn(name="stock")
     */
    private $stock;

    /**
     * @var Project
     *
     * @ORM\ManyToOne(targetEntity="Rbs\Bundle\CoreBundle\Entity\Project")
     * @ORM\JoinColumn(name="from_factory", nullable=false)
     */
    private $fromFactory;

    /**
     * @var Warehouse
     *
     * @ORM\ManyToOne(targetEntity="Rbs\Bundle\CoreBundle\Entity\Warehouse")
     * @ORM\JoinColumn(name="to_warehouse", nullable=false)
     */
    private $toWarehouse;

    /**
     * @var text
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    private $description;

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
     * @return Project
     */
    public function getFromFactory()
    {
        return $this->fromFactory;
    }

    /**
     * @param Project $fromFactory
     */
    public function setFromFactory($fromFactory)
    {
        $this->fromFactory = $fromFactory;
    }

    /**
     * @return Warehouse
     */
    public function getToWarehouse()
    {
        return $this->toWarehouse;
    }

    /**
     * @param Warehouse $toWarehouse
     */
    public function setToWarehouse($toWarehouse)
    {
        $this->toWarehouse = $toWarehouse;
    }

    /**
     * @return Stock
     */
    public function getStock()
    {
        return $this->stock;
    }

    /**
     * @param Stock $stock
     */
    public function setStock($stock)
    {
        $this->stock = $stock;
    }

    /**
     * @return text
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param text $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }
}