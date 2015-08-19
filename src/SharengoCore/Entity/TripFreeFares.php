<?php

namespace SharengoCore\Entity;

use SharengoCore\Utils\Interval;

use Doctrine\ORM\Mapping as ORM;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;

/**
 * TripFreeFares
 *
 * @ORM\Table(name="trip_free_fares")
 * @ORM\Entity(repositoryClass="SharengoCore\Entity\Repository\TripFreeFaresRepository")
 */
class TripFreeFares
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="trip_free_fares_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var Trips
     *
     * @ORM\ManyToOne(targetEntity="Trips")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="trip_id", referencedColumnName="id")
     * })
     */
    private $trip;

    /**
     * @var FreeFares
     *
     * @ORM\ManyToOne(targetEntity="FreeFares")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="free_fare_id", referencedColumnName="id")
     * })
     */
    private $freeFare;

    /**
     * @var integer
     *
     * @ORM\Column(name="minutes", type="integer", nullable=false)
     */
    private $minutes = 0;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="timestamp_beginning", type="datetimetz", nullable=false)
     */
    private $timestampBeginning;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="timestamp_end", type="datetimetz", nullable=false)
     */
    private $timestampEnd;

    /**
     * @var string
     *
     * @ORM\Column(name="notes", type="text", nullable=true)
     */
    private $notes;

    /**
     * @param DoctrineHydrator $hydrator
     * @return mixed
     */
    public function toArray(DoctrineHydrator $hydrator)
    {
        $tripFreeFare = $hydrator->extract($this);
        if ($tripFreeFare['trip'] != null) {
            $tripFreeFare['trip'] = $tripFreeFare['trip']->getId();
        }
        if ($tripFreeFare['freeFare'] != null) {
            $tripFreeFare['freeFare'] = $tripFreeFare['freeFare']->getId();
        }
        return $tripBill;
    }

    public static function createFromTripAndFreeFare(Trips $trip, FreeFares $freeFare)
    {
        $interval = new Interval($trip->getTimestampBeginning(), $trip->getTimestampEnd());

        return (new TripFreeFares())
            ->setTrip($trip)
            ->setFreeFare($freeFare)
            ->setMinutes($interval->minutes())
            ->setTimestampBeginning($trip->getTimestampBeginning())
            ->setTimestampEnd($trip->getTimestampEnd());
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set minutes
     *
     * @param integer $minutes
     *
     * @return TripFreeFares
     */
    public function setMinutes($minutes)
    {
        $this->minutes = $minutes;

        return $this;
    }

    /**
     * Get minutes
     *
     * @return integer
     */
    public function getMinutes()
    {
        return $this->minutes;
    }

    /**
     * Set timestampBeginning
     *
     * @param \DateTime $timestampBeginning
     *
     * @return TripFreeFares
     */
    public function setTimestampBeginning($timestampBeginning)
    {
        $this->timestampBeginning = $timestampBeginning;

        return $this;
    }

    /**
     * Get timestampBeginning
     *
     * @return \DateTime
     */
    public function getTimestampBeginning()
    {
        return $this->timestampBeginning;
    }

    /**
     * Set timestampEnd
     *
     * @param \DateTime $timestampEnd
     *
     * @return TripFreeFares
     */
    public function setTimestampEnd($timestampEnd)
    {
        $this->timestampEnd = $timestampEnd;

        return $this;
    }

    /**
     * Get timestampEnd
     *
     * @return \DateTime
     */
    public function getTimestampEnd()
    {
        return $this->timestampEnd;
    }

    /**
     * Set notes
     *
     * @param string $notes
     *
     * @return TripFreeFares
     */
    public function setNotes($notes)
    {
        $this->notes = $notes;

        return $this;
    }

    /**
     * Get notes
     *
     * @return string
     */
    public function getNotes()
    {
        return $this->notes;
    }

    /**
     * Set trip
     *
     * @param \SharengoCore\Entity\Trips $trip
     *
     * @return TripFreeFares
     */
    public function setTrip(Trips $trip = null)
    {
        $this->trip = $trip;

        return $this;
    }

    /**
     * Get trip
     *
     * @return \SharengoCore\Entity\Trips
     */
    public function getTrip()
    {
        return $this->trip;
    }

    /**
     * Set freeFare
     *
     * @param \SharengoCore\Entity\FreeFares $freeFare
     *
     * @return TripFreeFares
     */
    public function setFreeFare(FreeFares $freeFare = null)
    {
        $this->freeFare = $freeFare;

        return $this;
    }

    /**
     * Get trip
     *
     * @return \SharengoCore\Entity\FreeFares
     */
    public function getFreeFare()
    {
        return $this->freeFare;
    }
}
