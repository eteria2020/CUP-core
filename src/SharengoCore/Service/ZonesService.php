<?php

namespace SharengoCore\Service;

// Internals
use SharengoCore\Entity\Zone;
use SharengoCore\Entity\Repository\ZonesRepository;
// Externals
use Doctrine\ORM\EntityManager;

class ZonesService
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    private $zoneRepository;

    private $zoneAlarmsRepository;

    private $zoneGroupsRepository;

    private $zonePricesRepository;
    
    private $zoneBonusRepository;

    /**
     * @var DatatableServiceInterface
     */
    private $datatableService;

    /**
     * @param EntityManager $entityManager
     * @param DatatableServiceInterface $datatableService
     */
    public function __construct(
        EntityManager $entityManager,
        DatatableServiceInterface $datatableService
    ) {
        $this->entityManager = $entityManager;
        $this->zoneRepository = $this->entityManager->getRepository('\SharengoCore\Entity\Zone');
        $this->zoneAlarmsRepository = $this->entityManager->getRepository('\SharengoCore\Entity\ZoneAlarms');
        $this->zoneGroupsRepository = $this->entityManager->getRepository('\SharengoCore\Entity\ZoneGroups');
        $this->zonePricesRepository = $this->entityManager->getRepository('\SharengoCore\Entity\ZonePrices');
        $this->zoneBonusRepository = $this->entityManager->getRepository('\SharengoCore\Entity\ZoneBonus');
        $this->datatableService = $datatableService;
    }

    /**
     * @return mixed
     */
    public function getTotalZones()
    {
        return $this->zoneRepository->getTotalZones();
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
    
    public function getListZonesBonus()
    {
        return $this->zoneBonusRepository->findAll();
    }
    
    public function getListZonesBonusByFleet($fleet)
    {
        $bonusAreas = $this->zoneBonusRepository->findAllActiveByFleet($fleet);        
        
//        $bonusAreasByFleet = array();
//        foreach ($bonusAreas as $bonusArea) {
//            if (in_array($fleet_id, $zoneGroup->getFleetsId()))
//            {
//                $bonusAreasByFleet[] = $bonusArea;
//            }
//        }
        
        return $bonusAreas;
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

    public function getDataDataTable(array $as_filters = [], $count = false)
    {
        $zones = $this->datatableService->getData('Zone', $as_filters, $count);

        if ($count) {
            return $zones;
        }

        return array_map(function (Zone $zone) {
            return [
                'e' => [
                    'id' => $zone->getId(),
                    'name' => $zone->getName(),
                    'areaInvoice' => json_decode($zone->getAreaInvoiceJson(), true),
                    'active' => $zone->getActive(),
                    'hidden' => $zone->getHidden(),
                    'invoiceDescription' => $zone->getInvoiceDescription(),
                    'revGeo' => $zone->getRevGeo(),
                    'areaUse' => json_decode($zone->getAreaUseJson(), true),
                ],
                'button' => $zone->getId()
            ];
        }, $zones);
    }

    public function getZoneById($id)
    {
        return $this->zoneRepository->find($id);
    }

    /**
     * @param Zone $zone
     *
     * @return Zone
     */
    public function updateZone(Zone $zone)
    {
        $this->entityManager->persist($zone);
        $this->entityManager->flush();

        return $zone;
    }
    
    /**
     * @param array $zonesBonus, $longitude, $latitude
     * @return SharengoCore\Entity\ZoneBonus[]
     */
    public function checkPointInBonusZones(array $zonesBonus, $longitude, $latitude)
    {
        $zonesBonus_touched = array();
        foreach ($zonesBonus as $zoneBonus)
        {
            $inside = $this->zoneBonusRepository->findBonusZonesByCoordinatesAndFleet($zoneBonus, $longitude, $latitude);
            
            var_dump($inside);
            
            if ($inside)
                $zonesBonus_touched[] = $zoneBonus;
        }
        return $zonesBonus_touched;
    }
}
