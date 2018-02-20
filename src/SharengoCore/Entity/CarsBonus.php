<?php



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
     * @ORM\Column(name="car_plate", type="text", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="cars_bonus_car_plate_seq", allocationSize=1, initialValue=1)
     */
    private $carPlate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="nouse", type="datetimetz", nullable=true)
     */
    private $nouse;

    /**
     * @var boolean
     *
     * @ORM\Column(name="unplug_enable", type="boolean", nullable=false)
     */
    private $unplugEnable = false;


}

