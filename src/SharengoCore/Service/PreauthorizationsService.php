<?php

namespace SharengoCore\Service;

use Doctrine\ORM\EntityManager;
use SharengoCore\Entity\Preauthorizations;
use SharengoCore\Entity\Trips;
use SharengoCore\Entity\Customers;
use Cartasi\Entity\Transactions;
use SharengoCore\Entity\Repository\PreauthorizationsRepository;

class PreauthorizationsService
{
    /** @var EntityManager */
    private $entityManager;

    /**
     * @var PreauthorizationsRepository
     */
    private $preauthorizationsRepository;


    /**
     * @param $entityManager EntityManager
     * @param $preauthorizationsRepository PreauthorizationsRepository
     */
    public function __construct(
        EntityManager $entityManager,
        PreauthorizationsRepository $preauthorizationsRepository
    ) {
        $this->entityManager = $entityManager;
        $this->preauthorizationsRepository = $preauthorizationsRepository;
    }

    /**
     *
     * @param Trips $trip
     * @param string $message
     * @param Transactions|null $transaction
     * @param Customers $customer
     * @return Preauthorizations
     */
    public function generatePreauthorizations(Customers $customer, Trips $trip = null, Transactions $transaction = null)
    {
        $preauthorizations = new Preauthorizations($status = null, $statusFrom = null, $successfullyAt = null, $customer, $trip, $transaction);
        return $preauthorizations;
    }
}