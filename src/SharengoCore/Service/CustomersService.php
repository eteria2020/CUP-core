<?php

namespace SharengoCore\Service;

use SharengoCore\Entity\Customers;
use SharengoCore\Service\DatatableService;

use Zend\Authentication\AuthenticationService as UserService;

class CustomersService implements ValidatorServiceInterface
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
     * @var DatatableService
     */
    private $datatableService;

    /**
     * @param $entityManager
     */
    public function __construct($entityManager, UserService $userService, DatatableService $datatableService)
    {
        $this->entityManager = $entityManager;

        $this->clientRepository = $this->entityManager->getRepository('\SharengoCore\Entity\Customers');

        $this->userService = $userService;

        $this->datatableService = $datatableService;
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

    public function setCustomerDiscountRate(Customers $customer, $discount)
    {

        $customer->setDiscountRate(round($discount));

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

        $customers = $this->datatableService->getData('Customers', $as_filters);

        return array_map(function (Customers $customer) {
            return [
                'e-id'                  => $customer->getId(),
                'e-name'                => $customer->getName(),
                'e-surname'             => $customer->getSurname(),
                'e-mobile'              => $customer->getMobile(),
                'e-cardCode'            => is_object($customer->getCard()) ? $customer->getCard()->getCode() : '',
                'e-driverLicense'       => $customer->getDriverLicense(),
                'e-driverLicenseExpire' => is_object($customer->getDriverLicenseExpire()) ? $customer->getDriverLicenseExpire()->format('d-m-Y') : '',
                'e-email'               => $customer->getEmail(),
                'e-taxCode'             => $customer->getTaxCode(),
                'e-registration'        => $customer->getRegistrationCompleted() ? 'Completata' : 'Non Completata',
                'button'                => $customer->getId()
            ];
        }, $customers);
    }

    public function saveDriverLicense(Customers $customer)
    {
        $customer->setDriverLicenseCategories('{' . implode(',', $customer->getDriverLicenseCategories()) . '}');
        $this->entityManager->persist($customer);
        $this->entityManager->flush();

        // updates the identity in session
        $this->userService->getStorage()->write($customer);

        return $customer;
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

    /**
     * Persists customer
     *
     * @return Customers
     */
    public function saveData(Customers $customer)
    {
        $this->entityManager->persist($customer);
        $this->entityManager->flush();

        return $customer;
    }
}
