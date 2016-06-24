<?php

namespace SharengoCore\Service;

use SharengoCore\Entity\Events;
use SharengoCore\Entity\Trips;
use SharengoCore\Document\Repository\EventsRepository;

use Doctrine\ODM\MongoDB\DocumentRepository;

class EventsService
{
    /**
     * @var EventsRepository
     */
    private $eventsRepository;

    /**
     * @var EventsTypesService
     */
    private $eventsTypesService;

    public function __construct(
        EventsRepository $eventsRepository,
        EventsTypesService $eventsTypesService
    ) {
        $this->eventsRepository = $eventsRepository;
        $this->eventsTypesService = $eventsTypesService;
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
        $events = $this->eventsRepository->getByTrip($trip);

        foreach ($events as $event) {
            $eventType = $this->eventsTypesService->mapEvent($event);
            $event->setEventType($eventType);
        }

        return $events;
    }
}
