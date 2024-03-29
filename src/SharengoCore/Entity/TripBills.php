<?php

namespace SharengoCore\Entity;

use SharengoCore\Utils\Interval;

use Doctrine\ORM\Mapping as ORM;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;

/**
 * TripBills
 *
 * @ORM\Table(name="trip_bills")
 * @ORM\Entity(repositoryClass="SharengoCore\Entity\Repository\TripBillsRepository")
 */
class TripBills
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="trip_bills_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var Trips
     *
     * @ORM\ManyToOne(targetEntity="Trips", inversedBy="tripBills")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="trip_id", referencedColumnName="id")
     * })
     */
    private $trip;

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
        $tripBill = $hydrator->extract($this);
        if ($tripBill['trip'] != null) {
            $tripBill['trip'] = $tripBill['trip']->getId();
        }
        return $tripBill;
    }

    public static function createFromTrip(Trips $trip)
    {
        $interval = new Interval($trip->getTimestampBeginning(), $trip->getTimestampEnd());

        return (new TripBills())
            ->setTrip($trip)
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
     * @return TripBills
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
     * @return TripBills
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
     * @return TripBills
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
     * @return TripBills
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
     * @return TripBills
     */
    public function setTrip(\SharengoCore\Entity\Trips $trip = null)
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
}
