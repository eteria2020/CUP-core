<?php

namespace SharengoCore\Service;

use Doctrine\ORM\EntityManager;
use SharengoCore\Entity\Pois;
use SharengoCore\Entity\Repository\PoisRepository;

class PoisService
{
    /** @var EntityManager */
    private $entityManager;

    /**
     * @var PoisRepository
     */
    private $poisRepository;

    /** @var DatatableService */
    private $datatableService;

    const BRAND = "Share'n'Go";

    /**
     * @param $entityManager EntityManager
     * @param $poisRepository PoisRepository
     * @param $datatableService DatatableService
     */
    public function __construct(
        EntityManager $entityManager,
        PoisRepository $poisRepository,
        DatatableService $datatableService
    ) {
        $this->entityManager = $entityManager;
        $this->poisRepository = $poisRepository;
        $this->datatableService = $datatableService;
    }

    /**
     * @return mixed
     */
    public function getListPois()
    {
        return $this->poisRepository->findAll();
    }

    public function getTotalPois()
    {
        return $this->poisRepository->getTotalPois();
    }

    public function getPoiById($id)
    {
        return $this->poisRepository->find($id);
    }

    public function saveData(Pois $poi)
    {
        $poi->setBrand(self::BRAND);

        $this->entityManager->persist($poi);
        $this->entityManager->flush();
        return $poi;
    }

    public function deletePoi(Pois $poi)
    {
        $this->entityManager->remove($poi);
        $this->entityManager->flush();
    }

    public function getDataDataTable(array $as_filters = [])
    {
        $pois = $this->datatableService->getData('Pois', $as_filters);

        return array_map(function (Pois $poi) {
            return [
                'e'      => [
                    'id'                  => $poi->getId(),
                    'type'                => $poi->getType(),
                    'code'                => $poi->getCode(),
                    'name'                => $poi->getName(),
                    'address'             => $poi->getAddress(),
                    'town'                => $poi->getTown(),
                    'zipCode'             => $poi->getZipCode(),
                    'province'            => $poi->getProvince(),
                ],
                'button' => $poi->getId()
            ];
        }, $pois);
    }
}

