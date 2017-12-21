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

    private function _getPreviousEvent($plate, $startTime) {
        $q = $this->dm->createQueryBuilder('\SharengoCore\Document\Logs')
            ->field('carPlate')->equals($plate)
            ->field('eventTime')->lt($startTime)
            ->sort('eventTime', 'desc')
            ->getQuery();

        return $q->getSingleResult();
    }
}
