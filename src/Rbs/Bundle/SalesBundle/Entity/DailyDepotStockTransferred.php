<?php
namespace Rbs\Bundle\SalesBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Rbs\Bundle\CoreBundle\Entity\Item;
use Rbs\Bundle\CoreBundle\Entity\Depo;
use Symfony\Component\Validator\Constraints as Assert;
use Knp\DoctrineBehaviors\Model as ORMBehaviors;
use Xiidea\EasyAuditBundle\Annotation\ORMSubscribedEvents;

/**
 * DailyDepotStockTransferred
 *
 * @ORM\Table(name="sales_daily_hatchery_stock_transferred")
 * @ORM\Entity(repositoryClass="Rbs\Bundle\SalesBundle\Repository\DailyDepotStockTransferredRepository")
 * @ORMSubscribedEvents()
 */
class DailyDepotStockTransferred
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
     * @var DailyDepotStock
     *
     * @ORM\ManyToOne(targetEntity="Rbs\Bundle\SalesBundle\Entity\DailyDepotStock", inversedBy="dailyDepotStockTransferred")
     * @ORM\JoinColumn(name="daily_depot_stock_id", nullable=false)
     */
    private $dailyDepotStock;
    /**
     * @var Depo
     *
     * @ORM\ManyToOne(targetEntity="Rbs\Bundle\CoreBundle\Entity\Depo")
     * @ORM\JoinColumn(name="transferred_to_depot_id", nullable=true)
     */
    private $transferredToDepot;

    /**
     * @var Depo
     *
     * @ORM\ManyToOne(targetEntity="Rbs\Bundle\CoreBundle\Entity\Depo")
     * @ORM\JoinColumn(name="transferred_from_depot_id", nullable=true)
     */
    private $transferredFromDepot;

    /**
     * @var integer
     *
     * @ORM\Column(name="transferred_quantity", type="integer", options={"default" = 0})
     */
    private $transferredQuantity = 0;


    /**
     * @var integer
     *
     * @ORM\Column(name="received_quantity", type="integer", options={"default" = 0})
     */
    private $receivedQuantity = 0;

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
     * @return DailyDepotStock
     */
    public function getDailyDepotStock()
    {
        return $this->dailyDepotStock;
    }

    /**
     * @param DailyDepotStock $dailyDepotStock
     */
    public function setDailyDepotStock($dailyDepotStock)
    {
        $this->dailyDepotStock = $dailyDepotStock;
    }

    /**
     * @return Depo
     */
    public function getTransferredToDepot()
    {
        return $this->transferredToDepot;
    }

    /**
     * @param Depo $transferredToDepot
     */
    public function setTransferredToDepot($transferredToDepot)
    {
        $this->transferredToDepot = $transferredToDepot;
    }

    /**
     * @return Depo
     */
    public function getTransferredFromDepot()
    {
        return $this->transferredFromDepot;
    }

    /**
     * @param Depo $transferredFromDepot
     */
    public function setTransferredFromDepot($transferredFromDepot)
    {
        $this->transferredFromDepot = $transferredFromDepot;
    }


    /**
     * @return int
     */
    public function getTransferredQuantity()
    {
        return $this->transferredQuantity;
    }

    /**
     * @param int $transferredQuantity
     */
    public function setTransferredQuantity($transferredQuantity)
    {
        $this->transferredQuantity = $transferredQuantity;
    }

    /**
     * @return int
     */
    public function getReceivedQuantity()
    {
        return $this->receivedQuantity;
    }

    /**
     * @param int $receivedQuantity
     */
    public function setReceivedQuantity($receivedQuantity)
    {
        $this->receivedQuantity = $receivedQuantity;
    }



}