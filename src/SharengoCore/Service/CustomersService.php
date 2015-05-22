<?php

namespace SharengoCore\Service;

use SharengoCore\Entity\Customers;


class CustomersService
{

    private $entityManager;

    private $clientRepository;

    /**
     * @param $entityManager
     */
    public function __construct($entityManager)
    {
        $this->entityManager = $entityManager;

        $this->clientRepository = $this->entityManager->getRepository('\SharengoCore\Entity\Customers');
    }

    /**
     * @return mixed
     */
    public function getListCustomers()
    {
        return $this->clientRepository->findAll();
    }

    public function getUserByEmailPassword($s_username, $s_password)
    {
        return $this->clientRepository->getUserByEmailPassword($s_username, $s_password);

    }

    public function findByEmail($email)
    {
        return $this->clientRepository->findBy([
            'email' => $email
        ]);
    }

    public function findById($id)
    {
        return $this->clientRepository->findOneBy([
            'id' => $id
        ]);
    }

    public function findByTaxCode($taxCode)
    {
        return $this->clientRepository->findBy([
            'taxCode' => $taxCode
        ]);
    }

    public function findByDriversLicense($driversLicense)
    {
        return $this->clientRepository->findByCI('driverLicense', $driversLicense);
    }

    public function confirmFirstPaymentCompleted(Customers $customer) {

        $customer->setFirstPaymentCompleted(true);

        $this->entityManager->persist($customer);
        $this->entityManager->flush($customer);

    }

    public function setCustomerDiscountRate(Customers $customer, $discount) {

        $customer->setDiscountRate($discount);

        $this->entityManager->persist($customer);
        $this->entityManager->flush($customer);
        
    }

}
