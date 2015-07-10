<?php

namespace SharengoCore\Service;

use SharengoCore\Entity\Customers;
use SharengoCore\Entity\CustomersBonus;
use SharengoCore\Entity\PromoCodes;
use SharengoCore\Entity\Repository\CustomersBonusRepository;
use SharengoCore\Entity\Cards;
use SharengoCore\Service\DatatableService;

use Zend\Authentication\AuthenticationService as UserService;

class CustomersService implements ValidatorServiceInterface
{
    private $validatorEmail;

    private $validatorTaxCode;

    private $entityManager;

    private $clientRepository;

    /** @var  CustomersBonusRepository */
    private $customersBonusRepository;

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
     * @param UserService
     * @param DatatableService
     * @param CardsService
     * @param DoctrineHydrator
     */
    public function __construct(
        $entityManager,
        UserService $userService,
        DatatableService $datatableService,
        CardsService $cardsService
    ) {
        $this->entityManager = $entityManager;
        $this->clientRepository = $this->entityManager->getRepository('\SharengoCore\Entity\Customers');
        $this->customersBonusRepository = $this->entityManager->getRepository('\SharengoCore\Entity\CustomersBonus');
        $this->userService = $userService;
        $this->datatableService = $datatableService;
        $this->cardsService = $cardsService;
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

    public function getListCustomersFiltered($filters = [])
    {
        return $this->clientRepository->findBy($filters, ['surname' => 'ASC']);
    }

    public function getListCustomersFilteredLimited($filters = [], $limit)
    {
        return $this->clientRepository->findListCustomersFilteredLimited($filters, $limit);
    }

    public function getListMaintainersCards()
    {
        return $this->clientRepository->findMaintainersCards();
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
    }

    public function setCustomerDiscountRate(Customers $customer, $discount)
    {

        $customer->setDiscountRate(round($discount));

        $this->entityManager->persist($customer);
        $this->entityManager->flush($customer);
    }

    public function setCustomerReprofilingOption(Customers $customer, $option)
    {
        $customer->setReprofilingOption($option);

        $customer = $this->entityManager->merge($customer);
        $this->entityManager->persist($customer);
        $this->entityManager->flush($customer);

        return $customer;
    }

    public function enableApi(Customers $customer)
    {
        $customer->setEnabled(true);

        $this->entityManager->persist($customer);
        $this->entityManager->flush($customer);
    }

    public function increaseCustomerProfilingCounter(Customers $customer)
    {
        $customer->setProfilingCounter($customer->getProfilingCounter() + 1);

        $this->entityManager->persist($customer);
        $this->entityManager->flush($customer);

        return $customer;
    }

    public function getDataDataTable(array $as_filters = [])
    {
        $customers = $this->datatableService->getData('Customers', $as_filters);

        return array_map(function (Customers $customer) {
            return [
                'e'      => [
                    'id'                  => $customer->getId(),
                    'name'                => $customer->getName(),
                    'surname'             => $customer->getSurname(),
                    'mobile'              => $customer->getMobile(),
                    'driverLicense'       => $customer->getDriverLicense(),
                    'driverLicenseExpire' => is_object($customer->getDriverLicenseExpire()) ? $customer->getDriverLicenseExpire()->format('d-m-Y') : '',
                    'email'               => $customer->getEmail(),
                    'taxCode'             => $customer->getTaxCode(),
                    'registration'        => $customer->getRegistrationCompleted() ? 'Completata' : 'Non Completata',
                ],
                'cc'     => [
                    'code' => is_object($customer->getCard()) ? $customer->getCard()->getCode() : '',
                ],
                'button' => $customer->getId()
            ];
        }, $customers);
    }

    public function saveDriverLicense(Customers $customer)
    {
        $customer->setDriverLicenseCategories('{' . implode(',', $customer->getDriverLicenseCategories()) . '}');
        $this->entityManager->persist($customer);
        $this->entityManager->flush();

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

    public function addBonus(Customers $customer, CustomersBonus $bonus)
    {
        $bonus->setCustomer($customer);

        $this->entityManager->persist($bonus);
        $this->entityManager->flush();

        return $bonus;
    }

    public function getAllBonus(Customers $customer)
    {
        return $this->customersBonusRepository->findBy([
            'customer' => $customer
        ]);
    }

    public function findBonus($bonus)
    {
        return $this->customersBonusRepository->find($bonus);
    }

    /**
     * assign a Card to the Customer.
     * if the card is null, it first creates a virtual one
     */
    public function assignCard(Customers $customer, Cards $card = null, $isAssigned = null)
    {
        if (is_null($card)) {
            $card = $this->cardsService->createVirtualCard($customer);
        }

        if(!is_null($isAssigned) && $isAssigned === true) {
            $card->setIsAssigned(true);
        }

        $customer->setCard($card);

        $this->entityManager->persist($customer);
        $this->entityManager->flush();
    }

    public function removeCard(Customers $customer) {

        $card = $customer->getCard();
        $customer->setCard(null);
        $card->setIsAssigned(false);

        $this->entityManager->persist($card);
        $this->entityManager->persist($customer);

        $this->entityManager->flush();
    }

    public function checkUsedPromoCode(Customers $customers, PromoCodes $promoCode)
    {
        return $this->customersBonusRepository->checkUsedPromoCode($customers, $promoCode);
    }

    public function addBonusFromPromoCode(Customers $customers, PromoCodes $promoCode)
    {
        $customerBonus = CustomersBonus::createFromPromoCode($promoCode);

        $this->addBonus($customers, $customerBonus);
    }

    public function addBonusFromWebUser(Customers $customer, CustomersBonus $bonus)
    {
        $bonus->setType('promo');
        $bonus->setResidual($bonus->getTotal());
        $bonus->setInsertTs(new \DateTime());
        $bonus->setUpdateTs($bonus->getInsertTs());
        $bonus->setWebuser($this->userService->getIdentity());

        $this->addBonus($customer, $bonus);
    }

    public function removeBonus(CustomersBonus $customerBonus) {

        if($customerBonus->getTotal() == $customerBonus->getResidual()) {

            $this->entityManager->remove($customerBonus);
            $this->entityManager->flush();

            return true;
        }

        return false;
    }
}
