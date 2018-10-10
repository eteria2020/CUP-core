<?php

namespace SharengoCore\Service;

use SharengoCore\Document\Repository\IncidentsRepository;

use Doctrine\ODM\MongoDB\DocumentRepository;

class IncidentsService
{
    /**
     * @var IncidentsRepository
     */
    private $incidentsRepository;

    public function __construct(
        IncidentsRepository $incidentsRepository
        //EventsRepository $eventsRepository
    ) {
        $this->incidentsRepository = $incidentsRepository;
    }

    /**
     * @param integer $id
     * @return Events
     */
    public function getEventById($id)
    {
        return $this->eventsRepository->findOneById($id);
    }

    public function getIncidentByTrip($tripId)
    {
        $incident = $this->incidentsRepository->getByTrip($tripId);
        return $incident; 
    }
}
