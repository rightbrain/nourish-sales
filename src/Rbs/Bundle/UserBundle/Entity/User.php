<?php

namespace Rbs\Bundle\UserBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Knp\DoctrineBehaviors\Model as ORMBehaviors;

use Doctrine\Common\Collections\ArrayCollection;
use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;
use Rbs\Bundle\CoreBundle\Entity\Project;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Xiidea\EasyAuditBundle\Annotation\ORMSubscribedEvents;

/**
 * @ORM\Table(name="users")
 * @ORM\Entity(repositoryClass="Rbs\Bundle\UserBundle\Repository\UserRepository")
 * @ORMSubscribedEvents()
 * @UniqueEntity(
 *     fields={"email"},
 *     message="This email is already in use."
 * )
 * @UniqueEntity(
 *     fields={"username"},
 *     message="This username is already in use."
 * )
 */
class User extends BaseUser
{
    use ORMBehaviors\Timestampable\Timestampable,
        ORMBehaviors\SoftDeletable\SoftDeletable,
        ORMBehaviors\Blameable\Blameable;

    const RSM = 'RSM';
    const SR = 'SR';
    const AGENT = 'AGENT';
    const USER = 'USER';

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var array $type
     *
     * @ORM\Column(name="user_type", type="string", length=255, columnDefinition="ENUM('USER', 'RSM', 'SR', 'AGENT')")
     */
    private $userType;

    /**
     * @ORM\OneToOne(targetEntity="Profile", mappedBy="user", cascade={"persist"})
     */
    protected $profile;

    /**
     * @ORM\ManyToMany(targetEntity="Group", inversedBy="users")
     * @ORM\JoinTable(name="join_users_groups",
     *      joinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="group_id", referencedColumnName="id")}
     * )
     */
    protected $groups;

    /**
     * @ORM\ManyToMany(targetEntity="Rbs\Bundle\CoreBundle\Entity\Project", inversedBy="users")
     * @ORM\JoinTable(name="users_projects")
     **/
    private $projects;

    /**
     * @var integer
     * 
     * @ORM\Column(name="parent_id", type="integer", nullable=true)
     */
    protected $parentId;

    public function __construct()
    {
        parent::__construct();
        $this->projects = new ArrayCollection();
    }

    /**
     * @return Profile
     */
    public function getProfile()
    {
        return $this->profile;
    }

    /**
     * @param mixed $profile
     */
    public function setProfile($profile)
    {
        $profile->setUser($this);

        $this->profile = $profile;
    }

    public function isSuperAdmin()
    {
        $groups = $this->getGroups();
        foreach ($groups as $group) {
            if ($group->hasRole('ROLE_SUPER_ADMIN')) {
                return false;
            }
        }

        return parent::isSuperAdmin();
    }

    /**
     * @param Project $project
     * @return $this
     */
    public function addProject(Project $project)
    {
        if (!$this->getProjects()->contains($project)) {
            $this->projects->add($project);
        }

        return $this;
    }

    /**
     * @param Project $project
     */
    public function removeProject(Project $project)
    {
        $this->projects->removeElement($project);
    }

    /**
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getProjects()
    {
        return $this->projects;
    }

    /**
     * @return array
     */
    public function getUserType()
    {
        return $this->userType;
    }

    /**
     * @param array $userType
     */
    public function setUserType($userType)
    {
        $this->userType = $userType;
    }

    /**
     * @return mixed
     */
    public function getParentId()
    {
        return $this->parentId;
    }

    /**
     * @param mixed $parentId
     */
    public function setParentId($parentId)
    {
        $this->parentId = $parentId;
    }
}