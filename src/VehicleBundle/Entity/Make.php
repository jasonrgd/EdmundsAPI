<?php

namespace VehicleBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Make
 *
 * @ORM\Table(name="make")
 * @ORM\Entity(repositoryClass="VehicleBundle\Repository\MakeRepository")
 */
class Make
{
    /**
     * @var int
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
     * @var string
     *
     * @ORM\Column(name="makeNiceName", type="string", length=255)
     */
    private $makeNiceName;


    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return Make
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
     * Set makeNiceName
     *
     * @param string $makeNiceName
     *
     * @return Make
     */
    public function setMakeNiceName($makeNiceName)
    {
        $this->makeNiceName = $makeNiceName;

        return $this;
    }

    /**
     * Get makeNiceName
     *
     * @return string
     */
    public function getMakeNiceName()
    {
        return $this->makeNiceName;
    }
}

