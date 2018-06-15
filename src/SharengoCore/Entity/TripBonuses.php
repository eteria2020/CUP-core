<?php

namespace SharengoCore\Entity;

use SharengoCore\Utils\Interval;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;

use Doctrine\ORM\Mapping as ORM;

/**
 * TripBonuses
 *
 * @ORM\Table(name="trip_bonuses")
 * @ORM\Entity(repositoryClass="SharengoCore\Entity\Repository\TripBonusesRepository")
 */
class TripBonuses
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="trip_bonuses_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var Trips
     *
     * @ORM\ManyToOne(targetEntity="Trips", inversedBy="tripBonuses")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="trip_id", referencedColumnName="id")
     * })
     */
    private $trip;

    /**
     * @var CustomersBonus
     *
     * @ORM\ManyToOne(targetEntity="CustomersBonus")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="bonus_id", referencedColumnName="id")
     * })
     */
    private $bonus;

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
        $trip = $this->getTrip();

        $extractedTripBonus = $hydrator->extract($this);

        $extractedTripBonus['tripId'] = $trip->getId();

        unset($extractedTripBonus['bonus']);
        unset($extractedTripBonus['trip']);

        return $extractedTripBonus;
    }

    public static function createFromTripAndBonus(Trips $trip, CustomersBonus $bonus)
    {
        $interval = new Interval($trip->getTimestampBeginning(), $trip->getTimestampEnd());

        return (new TripBonuses())
            ->setTrip($trip)
            ->setBonus($bonus)
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
     * @return TripBonuses
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
     * @return TripBonuses
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
     * @return TripBonuses
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
     * @return TripBonuses
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
     * @return TripBonuses
     */
    public function setTrip(\SharengoCore\Entity\Trips $trip = null)
    {
        $this->trip = $trip;

        return $this;
    }

    /**
     * Get tripId
     *
     * @return \SharengoCore\Entity\Trips
     */
    public function getTrip()
    {
        return $this->trip;
    }

    /**
     * Set bonus
     *
     * @param \SharengoCore\Entity\CustomersBonus $bonus
     *
     * @return TripBonuses
     */
    public function setBonus(\SharengoCore\Entity\CustomersBonus $bonus = null)
    {
        $this->bonus = $bonus;

        return $this;
    }

    /**
     * Get bonus
     *
     * @return \SharengoCore\Entity\CustomersBonus
     */
    public function getBonus()
    {
        return $this->bonus;
    }
}
