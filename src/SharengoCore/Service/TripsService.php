<?php

namespace SharengoCore\Service;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping\Entity;
use SharengoCore\Entity\Repository\TripsRepository;

class TripsService
{
    /** @var TripsRepository */
    private $tripRepository;

    /**
     * @param EntityRepository $tripRepository
     */
    public function __construct($tripRepository)
    {
        $this->tripRepository = $tripRepository;
    }

    /**
     * @return mixed
     */
    public function getTripsByCustomer($customerId)
    {
        return $this->tripRepository->findTripsByCustomer($customerId);
    }
}
