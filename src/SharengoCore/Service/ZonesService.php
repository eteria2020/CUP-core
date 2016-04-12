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
    public function getListZones($showHidden = true, $showOnlyActive = false)
    {
        return $this->zoneRepository->findZonesWithMapCoords($showHidden, $showOnlyActive);
    }

    public function getListZonesAlarms()
    {
        return $this->zoneAlarmsRepository->findAll();
    }

    public function getListZonesPrices()
    {
        return $this->zonePricesRepository->findAll();
    }

    /**
     *  This method return a list of zone name for every
     *  zone group.
     *
     *  @return array<array<string>>
     */
    public function getListZonesGroups()
    {
        // Get all groups of zones
        $zoneGroups = $this->zoneGroupsRepository->findAll();

        foreach ($zoneGroups as $zoneGroup) {
            $zoneList = [];
            foreach ($zoneGroup->getZonesList() as $zoneId) {
                // For every zone, we extract the name.
                $zoneList[] = $this->zoneRepository->find($zoneId)->getName();
            }
            $zoneGroup->setZonesListText(implode(', ', $zoneList));
        }
        return $zoneGroups;
    }
}
