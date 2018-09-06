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
        $previousId = array($events[0]->getId());

        $plate = $trip->getCar()->getPlate();

        while ($is_same_trip) {
            $event = $this->_getPreviousEvent($plate, $startTime, $previousId);

            if ($event && $event->getTrip() == '0'
            && $event->getCustomerId() == $trip->getCustomer()->getId()) {
                array_unshift($events, $event);
                array_push($previousId, $event->getId());
                $startTime = $event->getEventTime();
            } else {
                $is_same_trip = false;
            }
        }

        return $events;
    }

    private function _getPreviousEvent($plate, $startTime, $previousId) {
        $q = $this->dm->createQueryBuilder('\SharengoCore\Document\Events')
            ->field('carPlate')->equals($plate)
            ->field('eventTime')->lt($startTime)
            ->field('id')->notIn($previousId)
            ->sort('eventTime', 'desc')
            ->getQuery();

        return $q->getSingleResult();
    }
}
