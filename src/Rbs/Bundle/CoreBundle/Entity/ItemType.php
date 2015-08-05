<?php
namespace Rbs\Bundle\CoreBundle\Entity;


use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Model as ORMBehaviors;

/**
 * ItemType
 *
 * @ORM\Table(name="item_types")
 * @ORM\Entity(repositoryClass="Rbs\Bundle\CoreBundle\Repository\ItemTypeRepository")
 */
class ItemType
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
     * @ORM\Column(name="item_types", type="string", length=255)
     */
    private $itemType;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Rbs\Bundle\CoreBundle\Entity\Item", mappedBy="itemType")
     */
    private $item;

    /**
     * @var ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="Rbs\Bundle\CoreBundle\Entity\Vendor", mappedBy="itemTypes")
     */
    private $vendors;

    /**
     * @var integer
     *
     * @ORM\Column(name="status", type="integer")
     */
    private $status;

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
     * Set itemType
     *
     * @param string $itemType
     * @return ItemType
     */
    public function setItemType($itemType)
    {
        $this->itemType = $itemType;

        return $this;
    }

    /**
     * Get itemType
     *
     * @return string
     */
    public function getItemType()
    {
        return $this->itemType;
    }

    /**
     * Set status
     *
     * @param integer $status
     * @return ItemType
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
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getItem()
    {
        return $this->item;
    }

    /**
     * @return ArrayCollection
     */
    public function getVendors()
    {
        return $this->vendors;
    }
}