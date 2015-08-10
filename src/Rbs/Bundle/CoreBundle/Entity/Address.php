<?php

namespace Rbs\Bundle\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Address
 *
 * @ORM\Table(name="address")
 * @ORM\Entity
 */
class Address
{
    /**
     * @var integer
     *
     * @ORM\Column(name="c1", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=250, nullable=false)
     */
    private $name;

    /**
     * @var integer
     *
     * @ORM\Column(name="level", type="integer", nullable=false)
     */
    private $level;

    /**
     * @var integer
     *
     * @ORM\Column(name="c4", type="integer", nullable=false)
     */
    private $c4;

    /**
     * @var integer
     *
     * @ORM\Column(name="code", type="integer", nullable=false)
     */
    private $code;

    /**
     * @var string
     *
     * @ORM\Column(name="c6", type="string", length=250, nullable=true)
     */
    private $c6;

    /**
     * @var string
     *
     * @ORM\Column(name="c7", type="string", length=250, nullable=true)
     */
    private $c7;

    /**
     * @var string
     *
     * @ORM\Column(name="c8", type="string", length=250, nullable=true)
     */
    private $c8;

    /**
     * @var string
     *
     * @ORM\Column(name="c9", type="string", length=250, nullable=true)
     */
    private $c9;

    public function __toString()
    {
        return $this->getName();
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Address
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set level
     *
     * @param integer $level
     * @return Address
     */
    public function setLevel($level)
    {
        $this->level = $level;

        return $this;
    }

    /**
     * Get level
     *
     * @return integer 
     */
    public function getLevel()
    {
        return $this->level;
    }

    /**
     * Set c4
     *
     * @param integer $c4
     * @return Address
     */
    public function setC4($c4)
    {
        $this->c4 = $c4;

        return $this;
    }

    /**
     * Get c4
     *
     * @return integer 
     */
    public function getC4()
    {
        return $this->c4;
    }

    /**
     * Set code
     *
     * @param integer $code
     * @return Address
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Get code
     *
     * @return integer 
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set c6
     *
     * @param string $c6
     * @return Address
     */
    public function setC6($c6)
    {
        $this->c6 = $c6;

        return $this;
    }

    /**
     * Get c6
     *
     * @return string 
     */
    public function getC6()
    {
        return $this->c6;
    }

    /**
     * Set c7
     *
     * @param string $c7
     * @return Address
     */
    public function setC7($c7)
    {
        $this->c7 = $c7;

        return $this;
    }

    /**
     * Get c7
     *
     * @return string 
     */
    public function getC7()
    {
        return $this->c7;
    }

    /**
     * Set c8
     *
     * @param string $c8
     * @return Address
     */
    public function setC8($c8)
    {
        $this->c8 = $c8;

        return $this;
    }

    /**
     * Get c8
     *
     * @return string 
     */
    public function getC8()
    {
        return $this->c8;
    }

    /**
     * Set c9
     *
     * @param string $c9
     * @return Address
     */
    public function setC9($c9)
    {
        $this->c9 = $c9;

        return $this;
    }

    /**
     * Get c9
     *
     * @return string 
     */
    public function getC9()
    {
        return $this->c9;
    }

    /**
     * Get c1
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }
}
