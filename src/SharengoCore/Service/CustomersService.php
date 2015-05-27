<?php

namespace SharengoCore\Service;

use SharengoCore\Entity\Customers;

use Zend\Authentication\AuthenticationService as UserService;

class CustomersService
{

    private $entityManager;

    private $clientRepository;

    /**
     * @var UserService
     */
    private $userService;

    /**
     * @param $entityManager
     */
    public function __construct($entityManager, UserService $userService)
    {
        $this->entityManager = $entityManager;

        $this->clientRepository = $this->entityManager->getRepository('\SharengoCore\Entity\Customers');

        $this->userService = $userService;
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
        return $this->clientRepository->findByCI('email', $email);
    }

    public function findById($id)
    {
        return $this->clientRepository->findOneBy([
            'id' => $id
        ]);
    }

    public function findByTaxCode($taxCode)
    {
        return $this->clientRepository->findByCI('taxCode', $taxCode);
    }

    public function findByDriversLicense($driversLicense)
    {
        return $this->clientRepository->findByCI('driverLicense', $driversLicense);
    }

    // the following methods have all the same structure, it stinks... need to refactor

    public function confirmFirstPaymentCompleted(Customers $customer)
    {
        $customer->setFirstPaymentCompleted(true);

        $this->entityManager->persist($customer);
        $this->entityManager->flush($customer);

        // updates the identity in session
        $this->userService->getStorage()->write($customer);
    }

    public function setCustomerDiscountRate(Customers $customer, $discount) {

        $customer->setDiscountRate($discount);

        $this->entityManager->persist($customer);
        $this->entityManager->flush($customer);

        // updates the identity in session
        $this->userService->getStorage()->write($customer);
    }

    public function setCustomerReprofilingOption(Customers $customer, $option)
    {
        $customer->setReprofilingOption($option);

        $customer = $this->entityManager->merge($customer);
        $this->entityManager->persist($customer);
        $this->entityManager->flush($customer);

        // updates the identity in session
        $this->userService->getStorage()->write($customer);

        return $customer;
    }

    public function increaseCustomerProfilingCounter(Customers $customer)
    {
        $customer->setProfilingCounter($customer->getProfilingCounter() + 1);

        $this->entityManager->persist($customer);
        $this->entityManager->flush($customer);

        // updates the identity in session
        $this->userService->getStorage()->write($customer);

        return $customer;
    }
}
