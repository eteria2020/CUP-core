<?php

namespace SharengoCore\Service;

use SharengoCore\Entity\Logs;
use SharengoCore\Entity\Trips;
use SharengoCore\Document\Repository\LogsRepository;

use Doctrine\ODM\MongoDB\DocumentRepository;

class LogsService
{
    /**
     * @var EventsRepository
     */
    private $logsRepository;

    public function __construct(
        LogsRepository $logsRepository
    ) {
        $this->logsRepository = $logsRepository;
    }

    /**
     * @param integer $id
     * @return logs
     */
    public function getLogtById($id)
    {
        return $this->logsRepository->findOneById($id);
    }

    /**
     * @param Trips $trip
     * @return Logs
     */
    public function getLogsByTrip(Trips $trip)
    {
        /*
        $logs = $this->logsRepository->getByTrip($trip);

        foreach ($logs as $log) {
            $eventType = $this->logsTypesService->mapEvent($event);
            $event->setEventType($eventType);
        }

        return $logs;
        */
        return $logs = $this->logsRepository->getByTrip($trip);
    }
}
