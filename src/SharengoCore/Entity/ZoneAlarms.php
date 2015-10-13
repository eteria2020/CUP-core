<?php

namespace SharengoCore\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ZoneAlarms
 *
 * @ORM\Table(name="zone_alarms")
 * @ORM\Entity
 */
class ZoneAlarms
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="zone_alarms_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="geo", type="string", nullable=false)
     */
    private $geo;

    /**
     * @var boolean
     *
     * @ORM\Column(name="active", type="boolean", nullable=false)
     */
    private $active;

    /**
     * @var SharengoCore\Entity\Fleet[]
     *
     * @ORM\ManyToMany(targetEntity="Fleet", mappedBy="zoneAlarms")
     */
    private $fleets;


    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return \SharengoCore\Entity\Fleet[]
     */
    public function getFleets()
    {
        return $this->fleets;
    }
}
