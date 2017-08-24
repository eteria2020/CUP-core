<?php

namespace SharengoCore\Service;

use SharengoCore\Entity\Customers;
use SharengoCore\Entity\CustomersPoints as Point;
use SharengoCore\Entity\Repository\CustomersPointsRepository as PointRepository;
use SharengoCore\Entity\Trips;
use SharengoCore\Utils\Interval;

use Doctrine\ORM\EntityManager;

class PointService
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var PointRepository
     */
    private $pointRepository;

    /**
     * @param EntityManager $entityManager
     * @param PointRepository $pointRepository
     */
    public function __construct(
        EntityManager $entityManager,
        PointRepository $pointRepository
    ) {
        $this->entityManager = $entityManager;
        $this->pointRepository = $pointRepository;
    }
    
}
