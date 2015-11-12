<?php

namespace SharengoCore\Service;

use SharengoCore\Entity\Events;
use SharengoCore\Entity\Trips;

use SharengoCore\Entity\Repository\EventsRepository;

class EventsService
{
    /**
     * @var EventsRepository
     */
    private $eventsRepository;

    public function __construct(EventsRepository $eventsRepository)
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
        return $this->eventsRepository->findByTrip($trip, ['id' => 'desc']);
    }
}
