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
     * @param boolean $isFromScript
     * @param boolean|null $saveToDb
     * @param boolean $clear clears EntityManager after flush()
     * @return DriversLicenseValidation
     */
    public function addFromResponse(
        Customers $customer,
        Response $response,
        array $data,
        $isFromScript = false,
        $saveToDb = true,
        $clear = false
    ) {
        $validation = $this->addFromData(
            $customer,
            $response->valid(),
            $response->code(),
            $response->message(),
            $data,
            $isFromScript,
            $saveToDb,
            $clear
        );

        return $validation;
    }

    /**
     * @param Customers $customer
     * @param boolean $valid
     * @param string $code
     * @param string $message
     * @param mixed[] $data
     * @param boolean $isFromScript
     * @param boolean|null $saveToDb
     * @param boolean $clear clears EntityManager after flush()
     * @return DriversLicenseValidation
     */
    public function addFromData(
        Customers $customer,
        $valid,
        $code,
        $message,
        array $data,
        $isFromScript = false,
        $saveToDb = true,
        $clear = false
    ) {
        $validation = new DriversLicenseValidation(
            $customer,
            $valid,
            $code,
            $message,
            $data,
            $isFromScript
        );

        if ($saveToDb) {
            $this->entityManager->persist($validation);
            $this->entityManager->flush();
            if ($clear) {
                $this->entityManager->clear();
            }
        }

        return $validation;
    }
}
