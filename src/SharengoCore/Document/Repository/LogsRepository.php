<?php

namespace SharengoCore\Document\Repository;

use Doctrine\ODM\MongoDB\DocumentRepository;
use SharengoCore\Document\Logs;

class LogsRepository extends DocumentRepository
{
    public function getByTrip($trip)
    {
        return $this->findBy([ "id_trip" => $trip->getId()]);
    }
    
}
