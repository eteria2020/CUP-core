<?php

namespace SharengoCore\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/**
 * Events
 *
 * @ODM\Document(collection="events", repositoryClass="SharengoCore\Document\Repository\EventsRepository")
 */
class Events
{
    /** @ODM\Id */
    private $id;

    /**
     * @var DateTime
     *
     * @ODM\Field(type="date", name="event_time");
     */
    private $eventTime;

    /**
     * @var integer
     *
     * @ODM\Field(type="integer", name="event_id");
     */
    private $eventId;

    /**
     * @var DateTime
     *
     * @ODM\Field(type="date", name="server_time");
     */
    private $serverTime;

    /**
     * @var string
     *
     * @ODM\Field(type="string", name="car_plate");
     */
    private $carPlate;

    /**
     * @var string
     *
     * @ODM\Field(type="string");
     */
    private $label;

    /**
     * @var integer
     *
     * @ODM\Field(type="integer");
     */
    private $level;

    /**
     * @var integer
     *
     * @ODM\Field(type="integer", name="customer_id");
     */
    private $customer;

    /**
     * @var string
     *
     * @ODM\Field(type="string", name="trip_id");
     */
    private $trip;

    /**
     * @var string
     *
     * @ODM\Field(type="string");
     */
    private $txtval;

    /**
     * @var integer
     *
     * @ODM\Field(type="integer");
     */
    private $intval;

    /**
     * @var float
     *
     * @ODM\Field(type="float");
     */
    private $lon;

    /**
     * @var float
     *
     * @ODM\Field(type="float");
     */
    private $lat;

    /**
     * @var integer
     *
     * @ODM\Field(type="integer");
     */
    private $km;

    /**
     * @var integer
     *
     * @ODM\Field(type="integer");
     */
    private $battery;

    /**
     * @var string
     *
     * @ODM\Field(type="string");
     */
    private $imei;

    /**
     * @var $eventType
     */
    private $eventType;

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
    public function getTrip()
    {
        return $this->trip;
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

    /**
     * @return string
     */
    public function getCarPlate()
    {
        return $this->carPlate;
    }

    /**
     * @return EventsType
     */
    public function getEventType()
    {
        return $this->eventType;
    }

    /**
     * Set EventType
     *
     * @param EventsTypes $eventTYpe
     *
     * @return EventsTypes
     */
    public function setEventType($eventType)
    {
        $this->eventType = $eventType;

        return $this;
    }
}
