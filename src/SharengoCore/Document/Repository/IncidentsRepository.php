<?php

namespace SharengoCore\Document\Repository;

use Doctrine\ODM\MongoDB\DocumentRepository;
use SharengoCore\Document\Incidents;

class IncidentsRepository extends DocumentRepository
{
    public function getByTrip($tripId)
    {
        $incident = $this->findBy([ "id" => (string)$tripId], array("serverTime", "desc"));
        return $incident;
    }
}
