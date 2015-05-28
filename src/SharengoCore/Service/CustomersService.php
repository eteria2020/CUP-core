<?php

namespace SharengoCore\Service;

use SharengoCore\Entity\Customers;

use Zend\Authentication\AuthenticationService as UserService;

class CustomersService
{
    private $validatorEmail;

    private $validatorTaxCode;

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

    public function getTotalCustomers()
    {
        return $this->clientRepository->getTotalCustomers();
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

    public function getDataDataTable(array $as_filters = [])
    {

        $ai_customers = $this->clientRepository->getDataTableCustomers(array(
                'limit'      => $as_filters['iDisplayLength'],
                'offset'     => $as_filters['iDisplayStart'],
                'column'     => $as_filters['column'],
                'columnSort' => $as_filters['iSortCol_0'],
                'order'      => $as_filters['sSortDir_0'],
                'search'     => $as_filters['searchValue'],
                'withLimit'  => $as_filters['withLimit']
            )
        );

        $as_data = array();

        /** @var Customers $I_customer */
        foreach ($ai_customers as $I_customer) {

            $as_data[] = array(
                'id'                  => $I_customer->getId(),
                'name'                => $I_customer->getName(),
                'surname'             => $I_customer->getSurname(),
                'mobile'              => $I_customer->getMobile(),
                'cardCode'            => $I_customer->getCardCode(),
                'driverLicense'       => $I_customer->getDriverLicense(),
                'driverLicenseExpire' => is_object($I_customer->getDriverLicenseExpire()) ? $I_customer->getDriverLicenseExpire()->format('d-m-Y') : '',
                'email'               => $I_customer->getEmail(),
                'taxCode'             => $I_customer->getTaxCode(),
                'registration'        => $I_customer->getRegistrationCompleted() ? 'Completata' : 'Non Completata',
                'button'              => $I_customer->getId()
            );
        }

        return $as_data;
    }

    /**
     * @return mixed
     */
    public function getValidatorEmail()
    {
        return $this->validatorEmail;
    }

    /**
     * @param mixed $validatorEmail
     */
    public function setValidatorEmail($validatorEmail)
    {
        $this->validatorEmail = $validatorEmail;
    }

    /**
     * @return mixed
     */
    public function getValidatorTaxCode()
    {
        return $this->validatorTaxCode;
    }

    /**
     * @param mixed $validatorTaxCode
     */
    public function setValidatorTaxCode($validatorTaxCode)
    {
        $this->validatorTaxCode = $validatorTaxCode;
    }
}
