<?php

namespace SharengoCore\Document\Repository;

use Doctrine\ODM\MongoDB\DocumentRepository;
use SharengoCore\Document\Events;

class EventsRepository extends DocumentRepository
{
    public function getByTrip($trip)
    {
        $events = $this->findBy([ "trip" => (string)$trip->getId()], array("eventTime", "asc"));
        $is_same_trip = true;

        if (!$events) {
            return array();
        }
        $startTime = $events[0]->getEventTime();
        $plate = $trip->getCar()->getPlate();

        while ($is_same_trip) {
            $event = $this->_getPreviousEvent($plate, $startTime);

            if ($event && $event->getTrip() == '0') {
                array_unshift($events, $event);
                $startTime = $event->getEventTime();
            } else {
                $is_same_trip = false;
            }
        }

        return $events;
    }

    private function _getPreviousEvent($plate, $startTime) {
        $q = $this->dm->createQueryBuilder('\SharengoCore\Document\Events')
            ->field('carPlate')->equals($plate)
            ->field('eventTime')->lt($startTime)
            ->sort('eventTime', 'desc')
            ->getQuery();

        return $q->getSingleResult();
    }
}
