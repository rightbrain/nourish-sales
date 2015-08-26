<?php
namespace Rbs\Bundle\SalesBundle\Entity;

use Rbs\Bundle\CoreBundle\Entity\Bundle;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Rbs\Bundle\CoreBundle\Entity\Project;
use Rbs\Bundle\CoreBundle\Entity\Warehouse;
use Symfony\Component\Validator\Constraints as Assert;
use Knp\DoctrineBehaviors\Model as ORMBehaviors;
use Xiidea\EasyAuditBundle\Annotation\ORMSubscribedEvents;

/**
 * Stock
 *
 * @ORM\Table(name="stocks")
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
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Rbs\Bundle\SalesBundle\Entity\Stock", mappedBy="stockHistory")
     */
    private $stock;

    /**
     * @var Project
     *
     * @ORM\ManyToOne(targetEntity="Rbs\Bundle\CoreBundle\Entity\Project")
     * @ORM\JoinColumn(name="from_factories", nullable=false)
     */
    private $fromFactory;

    /**
     * @var Warehouse
     *
     * @ORM\ManyToOne(targetEntity="Rbs\Bundle\CoreBundle\Entity\Warehouse")
     * @ORM\JoinColumn(name="to_warehouses", nullable=false)
     */
    private $toWarehouse;

    /**
     * @ORM\ManyToMany(targetEntity="Rbs\Bundle\CoreBundle\Entity\Bundle")
     **/
    private $bundles;

    public function __construct()
    {
        $this->bundles = new ArrayCollection();
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
     * Add bundle
     *
     * @param Bundle $bundle
     * @return $this
     */
    public function addBundle(Bundle $bundle)
    {
        if (!$this->getBundles()->contains($bundle)) {
            $this->bundles->add($bundle);
        }

        return $this;
    }

    /**
     * Remove bundle
     *
     * @param Bundle $bundle
     */
    public function removeBundle(Bundle $bundle)
    {
        $this->bundles->removeElement($bundle);
    }

    /**
     * Get bundle
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getBundles()
    {
        return $this->bundles;
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
     * @return ArrayCollection
     */
    public function getStock()
    {
        return $this->stock;
    }

    /**
     * @param ArrayCollection $stock
     */
    public function setStock($stock)
    {
        $this->stock = $stock;
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
}