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
     * @ODM\Field(type="string", name="label");
     */
    private $label;

    /**
     * @var integer
     *
     * MAYBE TO BE MAPPED
     */
    private $level;

    /**
     * @var integer
     *
     * @ODM\Field(type="integer", name="customer_id");
     */
    private $customer;

    /**
     * @var integer
     *
     * @ODM\Field(type="integer", name="trip_id");
     */
    private $trip;

    /**
     * @var string
     *
     * MAYBE TO BE MAPPED
     */
    private $txtval;

    /**
     * @var integer
     *
     * MAYBE TO BE MAPPED
     */
    private $intval;

    /**
     * @var string
     *
     * MAYBE TO BE MAPPED
     */
    private $geo;

    /**
     * @var string
     *
     * MAYBE TO BE MAPPED
     */
    private $lon;

    /**
     * @var string
     *
     * MAYBE TO BE MAPPED
     */
    private $lat;

    /**
     * @var integer
     *
     * MAYBE TO BE MAPPED
     */
    private $km;

    /**
     * @var integer
     *
     * MAYBE TO BE MAPPED
     */
    private $battery;

    /**
     * @var string
     *
     * MAYBE TO BE MAPPED
     */
    private $mac;

    /**
     * @var string
     *
     * MAYBE TO BE MAPPED
     */
    private $imei;

    /**
     * @var array
     *
     * MAYBE TO BE MAPPED
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
