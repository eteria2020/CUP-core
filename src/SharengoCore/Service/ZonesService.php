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
        $this->zoneGroupsRepository = $this->entityManager->getRepository('\SharengoCore\Entity\ZoneGroups');
        $this->zonePricesRepository = $this->entityManager->getRepository('\SharengoCore\Entity\ZonePrices');
    }

    /**
     *  @return mixed
     *  @param showHidden bool Specify if return only the zone with property "hidden = true".
     *  @param showOnlyActive bool Specify if return only the zone with property "active = true".
     */
    public function getListZones($showHidden = true,$showOnlyActive = false)
    {
        return $this->zoneRepository->findZonesWithMapCoords($showHidden,$showOnlyActive);
    }

    public function getListZonesAlarms()
    {
        return $this->zoneAlarmsRepository->findAll();
    }

    public function getListZonesPrices()
    {
        return $this->zonePricesRepository->findAll();
    }

    public function getListZonesGroups() {
        $zoneGroups = $this->zoneGroupsRepository->findAll();

        $zoneList = [];
        foreach($zoneGroups as $zoneGroup) {
            foreach($zoneGroup->getZonesList() as $zoneId) {
                $zoneList[] = $this->zoneRepository->find($zoneId)->getName();
            }
            $zoneGroup->setZonesListText(implode(', ', $zoneList));
        }

        return $zoneGroups;
    }

}
