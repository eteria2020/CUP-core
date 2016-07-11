<?php

namespace SharengoCore\Service;

use MvLabsDriversLicenseValidation\Response;
use SharengoCore\Entity\DriversLicenseValidation;
use SharengoCore\Entity\Repository\DriversLicenseValidationRepository;

use Doctrine\ORM\EntityManager;

class DriversLicenseValidationService
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var DriversLicenseValidationRepository
     */
    private $repository;

    /**
     * @param EntityManager $entityManager
     * @param DriversLicenseValidationRepository $repository
     */
    public function __construct(
        EntityManager $entityManager,
        DriversLicenseValidationRepository $repository
    ) {
        $this->entityManager = $entityManager;
        $this->repository = $repository;
    }

    /**
     * @param Customers $customer
     * @param Response $response
     * @return DriversLicenseValidation
     */
    public function addFromResponse(Customers $customer, Response $response)
    {
        $validation = new DriversLicenseValidation(
            $customer,
            $response->valid(),
            $response->code(),
            $response->message()
        );

        $this->entityManager->persist($validation);
        $this->entityManager->flush();

        return $validation;
    }

    /**
     * @param Customers $customer
     * @param boolean $valid
     * @param string $code
     * @param string $message
     * @return DriversLicenseValidation
     */
    public function addFromData(Customers $customer, $valid, $code, $message)
    {
        $validation = new DriversLicenseValidation(
            $customer,
            $valid,
            $code,
            $message
        );

        $this->entityManager->persist($validation);
        $this->entityManager->flush();

        return $validation;
    }
}
