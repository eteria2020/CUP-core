<?php

namespace SharengoCore\Service;

use SharengoCore\Entity\Events;
use SharengoCore\Entity\Trips;

use Doctrine\ODM\MongoDB\DocumentRepository;

class EventsService
{
    /**
     * @var EventsRepository
     */
    private $eventsRepository;

    public function __construct(DocumentRepository $eventsRepository)
    {
        $this->eventsRepository = $eventsRepository;
    }

    /**
     * @param integer $id
     * @return Events
     */
    public function getEventById($id)
    {
        return $this->eventsRepository->findOneById($id);
    }

    /**
     * @param Trips $trip
     * @return Events
     */
    public function getEventsByTrip(Trips $trip)
    {
        return $this->eventsRepository->findBy([ "trip" =>  $trip->getId()]);
    }
}
