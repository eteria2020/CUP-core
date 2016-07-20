<?php

namespace SharengoCore\Service;

use MvLabsDriversLicenseValidation\Response\Response;
use SharengoCore\Entity\Customers;
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
     * @return [DriversLicenseValidation]
     */
    public function getByCustomer(Customers $customer)
    {
        return $this->repository->findByCustomer($customer);
    }

    /**
     * @param Customers $customer
     * @param Response $response
     * @param mixed[] $data
     * @param boolean|null $saveToDb
     * @return DriversLicenseValidation
     */
    public function addFromResponse(
        Customers $customer,
        Response $response,
        array $data,
        $saveToDb = true
    ) {
        $validation = new DriversLicenseValidation(
            $customer,
            $response->valid(),
            $response->code(),
            $response->message(),
            $data
        );

        if ($saveToDb) {
            $this->entityManager->persist($validation);
            $this->entityManager->flush();
        }

        return $validation;
    }

    /**
     * @param Customers $customer
     * @param boolean $valid
     * @param string $code
     * @param string $message
     * @param mixed[] $data
     * @param boolean|null $saveToDb
     * @return DriversLicenseValidation
     */
    public function addFromData(
        Customers $customer,
        $valid,
        $code,
        $message,
        array $data,
        $saveToDb = true
    ) {
        $validation = new DriversLicenseValidation(
            $customer,
            $valid,
            $code,
            $message,
            $data
        );

        if ($saveToDb) {
            $this->entityManager->persist($validation);
            $this->entityManager->flush();
        }

        return $validation;
    }
}
