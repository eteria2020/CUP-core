<?php

namespace SharengoCore\Service;

use Doctrine\ORM\EntityManager;
use SharengoCore\Entity\Zone;


class ZonesService
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    private $zoneRepository;

    private $zoneAlarmsRepository;

    /**
     * @param EntityManager               $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->zoneRepository = $this->entityManager->getRepository('\SharengoCore\Entity\Zone');
        $this->zoneAlarmsRepository = $this->entityManager->getRepository('\SharengoCore\Entity\ZoneAlarms');
    }

    /**
     * @return mixed
     */
    public function getListZones()
    {
        return $this->zoneRepository->findAll();
    }

    public function getListZonesAlarms()
    {
        return $this->zoneAlarmsRepository->findAll();
    }

}
