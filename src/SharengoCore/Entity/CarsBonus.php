<?php

namespace SharengoCore\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CarsBonus
 *
 * @ORM\Table(name="cars_bonus")
 * @ORM\Entity(repositoryClass="SharengoCore\Entity\Repository\CarsBonusRepository")
 */
class CarsBonus
{
    /**
     * @var string
     *
     * @ORM\OneToOne(targetEntity="Cars", inversedBy="carsBonus")
     * @ORM\JoinColumn(name="car_plate", referencedColumnName="plate")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $plate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="nouse", type="datetime", nullable=true)
     */
    private $nouse;

    /**
     * @var boolean
     *
     * @ORM\Column(name="unplug_enable", type="boolean", nullable=false)
     */
    private $unplugEnable = false;

    /**
     * @var integer
     *
     * @ORM\Column(name="free_x", type="integer", nullable=true)
     */
    private $freeX;

    /**
     *
     * @return datetime
     */
    public function getNouse()
    {
        return $this->nouse;
    }

    /**
     *
     * @return boolean
     */
    public function getUnplugEnable()
    {
        $result = false;

        if (!is_null($this->unplugEnable)) {
            $result = $this->unplugEnable;
        }

        return $result;
    }

    /**
     * Get freeX
     *
     * @return integer
     */
    public function getFreeX()
    {
        return $this->freeX;
    }

    /**
     * Set freeX
     *
     * @param integer $name
     *
     * @return CarsBonus
     */
    public function setFreeX($val)
    {
        $this->freeX = $val;
        return $this;
    }
}

