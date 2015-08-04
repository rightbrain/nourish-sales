<?php
namespace Rbs\Bundle\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Model as ORMBehaviors;

/**
 * Project
 *
 * @ORM\Table(name="project_types")
 * @ORM\Entity(repositoryClass="Rbs\Bundle\CoreBundle\Repository\ProjectTypeRepository")
 */
class ProjectType
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
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

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
     * Set projectCategoryName
     *
     * @param string $projectCategoryName
     * @return ProjectType
     */
    public function setProjectCategoryName($projectCategoryName)
    {
        $this->name = $projectCategoryName;

        return $this;
    }

    /**
     * Get projectCategoryName
     *
     * @return string
     */
    public function getProjectCategoryName()
    {
        return $this->getName();
    }

    /**
     * Set status
     *
     * @param integer $status
     * @return ProjectType
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
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

}