<?php

namespace SharengoCore\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

/**
 * Events
 *
 * @ODM\Document(collection="incident", repositoryClass="SharengoCore\Document\Repository\IncidentsRepository")
 */
class Incidents
{
    /** @ODM\Id */
    private $_id;

    /**
     * @var string
     *
     * @ODM\Field(type="string", name="state");
     */
    private $state;

    /**
     * @var string
     *
     * @ODM\Field(type="string", name="errorCode");
     */
    private $errorCode;
    
    /**
     * @var collection
     *
     * @ODM\Field(type="collection", name="data");
     */
    private $data;

    /**
     * @var string
     *
     * @ODM\Field(type="string", name="errorDescription");
     */
    private $errorDescription;

    /**
     * @var DateTime
     *
     * @ODM\Field(type="date", name="server_time");
     */
    private $serverTime;

    /**
     * @var string
     *
     * @ODM\Field(type="string", name="id");
     */
    private $tripId;    

    function getId() {
        return $this->_id;
    }

    function getState() {
        return $this->state;
    }

    function getErrorCode() {
        return $this->errorCode;
    }

    function getErrorDescription() {
        return $this->errorDescription;
    }

    function getServerTime() {
        return $this->serverTime;
    }
    
    function getTripId() {
        return $this->tripId;
    }
    
    function getData() {
        return $this->data;
    }

    
}
