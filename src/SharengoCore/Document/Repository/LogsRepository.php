<?php

namespace SharengoCore\Document\Repository;

use Doctrine\ODM\MongoDB\DocumentRepository;
use SharengoCore\Document\Logs;

class LogsRepository extends DocumentRepository
{
    public function getByTrip($trip)
    {
        $a = "";
        $logs = $this->findBy([ "id_trip" => $trip->getId()]);
        //2095638
        //1081214
        //$logs = $this->findBy([ "id_trip" => 2095638 ]);
        //$logs = $this->findBy([ "lon" => 9.20278]);
        $a = "";
        
        //$logs = $this->findBy("id_trip", $trip->getId());
        
        /*
        $is_same_trip = true;

        
        if (!$logs) {
            return array();
        }
        
        $startTime = $events[0]->getEventTime();

        while ($is_same_trip) {
            $event = $this->_getPreviousEvent($plate, $startTime);

            if ($event && $event->getTrip() == '0'
            && $event->getCustomerId() == $trip->getCustomer()->getId()) {
                array_unshift($events, $event);
                $startTime = $event->getEventTime();
            } else {
                $is_same_trip = false;
            }
        }
        */
        return $logs;
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
