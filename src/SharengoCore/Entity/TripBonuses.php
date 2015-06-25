<?php

namespace SharengoCore\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TripBonuses
 *
 * @ORM\Table(name="trip_bonuses")
 * @ORM\Entity
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
     * @ORM\ManyToOne(targetEntity="Trips")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="trip_id", referencedColumnName="id")
     * })
     */
    private $tripId;

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

    public static function createFromTrip(Trips $trip)
    {
        $interval = new Interval($trip->getTimestampBeginning(), $trip->getTimestampEnd());

        return (new TripBonuses())->setTripId($trip->getTripId())
            ->setMinutes($interval->minutes())
            ->setTimestampBeginning($trip->getTimestampBeginning())
            ->setTimestampEnd($trip->getTimestampEnd());
    }
}