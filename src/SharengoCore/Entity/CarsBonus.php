<?php

namespace SharengoCore\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CarsBonus
 *
 * @ORM\Table(name="cars_bonus")
 * @ORM\Entity
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
     * 
     * @return datetime
     */
    public function getNouse() {
        return $this->nouse;
    }

    /**
     * 
     * @return boolean
     */
    public function getUnplugEnable() {
        return $this->unplugEnable;
    }

}

