<?php
namespace Rbs\Bundle\SalesBundle\Entity;

use Rbs\Bundle\CoreBundle\Entity\Bundle;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Rbs\Bundle\CoreBundle\Entity\Item;
use Symfony\Component\Validator\Constraints as Assert;
use Knp\DoctrineBehaviors\Model as ORMBehaviors;
use Xiidea\EasyAuditBundle\Annotation\ORMSubscribedEvents;

/**
 * Stock
 *
 * @ORM\Table(name="stocks")
 * @ORM\Entity(repositoryClass="Rbs\Bundle\SalesBundle\Repository\StockRepository")
 * @ORMSubscribedEvents()
 */
class Stock
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
     * @var Item
     *
     * @ORM\OneToOne(targetEntity="Rbs\Bundle\CoreBundle\Entity\Item")
     * @ORM\JoinColumn(name="items", nullable=false)
     */
    private $item;

    /**
     * @var StockHistory
     *
     * @ORM\ManyToOne(targetEntity="Rbs\Bundle\SalesBundle\Entity\StockHistory", inversedBy="stock")
     * @ORM\JoinColumn(name="stock_history")
     */
    private $stockHistory;

    /**
     * @var integer
     *
     * @ORM\Column(name="on_hand", type="integer", nullable=true)
     */
    private $onHand;

    /**
     * @var integer
     *
     * @ORM\Column(name="on_hold", type="integer", nullable=true)
     */
    private $onHold;

    /**
     * @var integer
     *
     * @ORM\Column(name="available_on_demand", type="integer", nullable=true)
     */
    private $availableOnDemand;

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
    public function getOnHand()
    {
        return $this->onHand;
    }

    /**
     * @param int $onHand
     */
    public function setOnHand($onHand)
    {
        $this->onHand = $onHand;
    }

    /**
     * @return int
     */
    public function getOnHold()
    {
        return $this->onHold;
    }

    /**
     * @param int $onHold
     */
    public function setOnHold($onHold)
    {
        $this->onHold = $onHold;
    }

    /**
     * @return int
     */
    public function getAvailableOnDemand()
    {
        return $this->availableOnDemand;
    }

    /**
     * @param int $availableOnDemand
     */
    public function setAvailableOnDemand($availableOnDemand)
    {
        $this->availableOnDemand = $availableOnDemand;
    }

    /**
     * @return StockHistory
     */
    public function getStockHistory()
    {
        return $this->stockHistory;
    }

    /**
     * @param StockHistory $stockHistory
     */
    public function setStockHistory($stockHistory)
    {
        $this->stockHistory = $stockHistory;
    }

    /**
     * @return Item
     */
    public function getItem()
    {
        return $this->item;
    }

    /**
     * @param Item $item
     */
    public function setItem($item)
    {
        $this->item = $item;
    }
}