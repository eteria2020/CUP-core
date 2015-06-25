<?php

namespace SharengoCore\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TripBills
 *
 * @ORM\Table(name="trip_bills")
 * @ORM\Entity
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
     * @var integer
     *
     * @ORM\Column(name="cost", type="integer", nullable= false)
     */
    private $cost = 0;

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

        return (new TripBills())->setTripId($trip->getTripId())
            ->setMinutes($interval->minutes())
            ->setCost($trip->getPriceCent() + $trip->getVatCent())
            ->setTimestampBeginning($trip->getTimestampBeginning())
            ->setTimestampEnd($trip->getTimestampEnd());
    }
}