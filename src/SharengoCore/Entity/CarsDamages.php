<?php

namespace SharengoCore\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CarDemages
 *
 * @ORM\Table(name="cars_damages")
 * @ORM\Entity(repositoryClass="SharengoCore\Entity\Repository\CarsDamagesRepository")
 */
class CarsDamages
{
    /**
     * @var string
     *
     * @ORM\Column(name="name", type="text", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $name;

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
     * Set name
     *
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }
}
