<?php

namespace SharengoCore\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CarsBonusHistory
 *
 * @ORM\Table(name="cars_bonus_history", indexes={@ORM\Index(name="IDX_7938C137719ED75B", columns={"plate"})})
 * @ORM\Entity
 */
class CarsBonusHistory
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="cars_bonus_history_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="inserted_ts", type="datetime", nullable=false)
     */
    private $insertedTs;

    /**
     * @var integer
     *
     * @ORM\Column(name="free_x", type="integer", nullable=true)
     */
    private $freeX;

    /**
     * @var boolean
     *
     * @ORM\Column(name="permanance", type="boolean", nullable=false)
     */
    private $permanance;

    /**
     * @var \Cars
     *
     * @ORM\ManyToOne(targetEntity="Cars")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="plate", referencedColumnName="plate")
     * })
     */
    private $plate;
    
    public function __construct($freeX, $permanance, $plate)
    {
        $this->insertedTs = new \DateTime();
        $this->freeX = $freeX;
        $this->permanance = $permanance;
        $this->plate = $plate;
    }


}

