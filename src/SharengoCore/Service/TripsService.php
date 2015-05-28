<?php

namespace SharengoCore\Service;

use Doctrine\ORM\Mapping\Entity;
use SharengoCore\Entity\Repository\TripsRepository;

class TripsService
{
    /**
     * @var Entity
     */
    private $entityManager;

    /** @var TripsRepository */
    private $tripRepository;

    /**
     * @param $entityManager
     */
    public function __construct($entityManager)
    {
        $this->entityManager = $entityManager;

        $this->tripRepository = $this->entityManager->getRepository('\SharengoCore\Entity\Trips');
    }

    /**
     * @return mixed
     */
    public function getTripsByCustomer($customerId)
    {
        return $this->tripRepository->findTripsByCustomer($customerId);
    }
}
