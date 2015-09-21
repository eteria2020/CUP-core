<?php

namespace SharengoCore\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Events
 *
 * @ORM\Table(name="events")
 * @ORM\Entity(repositoryClass="SharengoCore\Entity\Repository\EventsRepository")
 */
class Events
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="events_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="event_time", type="datetime", nullable=true)
     */
    private $eventTime;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="server_time", type="datetime", nullable=true)
     */
    private $serverTime;

    /**
     * @var string
     *
     * @ORM\Column(name="car_plate", type="string", nullable=true)
     */
    private $carPlate;

    /**
     * @var integer
     *
     * @ORM\Column(name="event_id", type="integer", nullable=true)
     */
    private $eventId;

    /**
     * @var string
     *
     * @ORM\Column(name="label", type="string", nullable=true)
     */
    private $label;

    /**
     * @var integer
     *
     * @ORM\Column(name="level", type="integer", nullable=true)
     */
    private $level;

    /**
     * @var integer
     *
     * @ORM\Column(name="customer_id", type="integer", nullable=true)
     */
    private $customer;

    /**
     * @var integer
     *
     * @ORM\Column(name="trip_id", type="integer", nullable=true)
     */
    private $trip;

    /**
     * @var string
     *
     * @ORM\Column(name="txtval", type="string", nullable=true)
     */
    private $txtval;

    /**
     * @var integer
     *
     * @ORM\Column(name="intval", type="integer", nullable=true)
     */
    private $intval;

    /**
     * @var string
     *
     * @ORM\Column(name="geo", type="string", nullable=true)
     */
    private $geo;

    /**
     * @var string
     *
     * @ORM\Column(name="lon", type="decimal", precision=10, scale=0, nullable=true)
     */
    private $lon;

    /**
     * @var string
     *
     * @ORM\Column(name="lat", type="decimal", precision=10, scale=0, nullable=true)
     */
    private $lat;

    /**
     * @var integer
     *
     * @ORM\Column(name="km", type="integer", nullable=true)
     */
    private $km;

    /**
     * @var integer
     *
     * @ORM\Column(name="battery", type="integer", nullable=true)
     */
    private $battery;

    /**
     * @var string
     *
     * @ORM\Column(name="mac", type="string", nullable=true)
     */
    private $mac;

    /**
     * @var string
     *
     * @ORM\Column(name="imei", type="string", nullable=true)
     */
    private $imei;

    /**
     * @var array
     *
     * @ORM\Column(name="data", type="json_array", nullable=true)
     */
    private $data;

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
     * @return \DateTime
     */
    public function getEventTime()
    {
        return $this->eventTime;
    }

    /**
     * @return integer
     */
    public function getEventId()
    {
        return $this->eventId;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @return string
     */
    public function getTxtval()
    {
        return $this->txtval;
    }

    /**
     * @return integer
     */
    public function getIntval()
    {
        return $this->intval;
    }

    /**
     * @return integer
     */
    public function getKm()
    {
        return $this->km;
    }

    /**
     * @return integer
     */
    public function getBattery()
    {
        return $this->battery;
    }

    /**
     * @return string
     */
    public function getLon()
    {
        return $this->lon;
    }

    /**
     * @return string
     */
    public function getLat()
    {
        return $this->lat;
    }
}
