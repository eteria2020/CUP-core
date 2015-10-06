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
     * @var \SharengoCore\Entity\Fleet
     *
     * @ORM\OneToOne(targetEntity="SharengoCore\Entity\Fleet")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="fleet_id", referencedColumnName="id")
     * })
     */
    private $fleet;


    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return \SharengoCore\Entity\Fleet
     */
    public function getFleet()
    {
        return $this->fleet;
    }

    /**
     * @param \SharengoCore\Entity\Fleet $fleet
     *
     * @return ZoneAlarms
     */
    public function setFleet($fleet)
    {
        $this->fleet = $fleet;

        return $this;
    }
}
