<?php

namespace SharengoCore\Service;

use SharengoCore\Entity\Customers;
use SharengoCore\Entity\CustomersBonus;
use SharengoCore\Entity\PromoCodes;
use SharengoCore\Entity\Repository\CustomersBonusRepository;
use SharengoCore\Entity\Cards;
use SharengoCore\Service\DatatableService;
use SharengoCore\Service\SimpleLoggerService as Logger;

use Zend\Authentication\AuthenticationService as UserService;

class CustomersService implements ValidatorServiceInterface
{
    private $validatorEmail;

    private $validatorTaxCode;

    private $entityManager;

    private $customersRepository;

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
     * @var EmailService
     */
    private $emailService;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @param $entityManager
     * @param UserService
     * @param DatatableService
     * @param CardsService
     * @param EmailService
     */
    public function __construct(
        $entityManager,
        UserService $userService,
        DatatableService $datatableService,
        CardsService $cardsService,
        EmailService $emailService,
        Logger $logger
    ) {
        $this->entityManager = $entityManager;
        $this->customersRepository = $this->entityManager->getRepository('\SharengoCore\Entity\Customers');
        $this->customersBonusRepository = $this->entityManager->getRepository('\SharengoCore\Entity\CustomersBonus');
        $this->userService = $userService;
        $this->datatableService = $datatableService;
        $this->cardsService = $cardsService;
        $this->emailService = $emailService;
        $this->logger = $logger;
    }

    /**
     * @return mixed
     */
    public function getListCustomers()
    {
        return $this->customersRepository->findAll();
    }

    public function getTotalCustomers()
    {
        return $this->customersRepository->getTotalCustomers();
    }

    public function getListCustomersFiltered($filters = [])
    {
        return $this->customersRepository->findBy($filters, ['surname' => 'ASC']);
    }

    public function getListCustomersFilteredLimited($filters = [], $limit)
    {
        return $this->customersRepository->findListCustomersFilteredLimited($filters, $limit);
    }

    public function getCustomersFirstPaymentCompletedNoInvoice()
    {
        return $this->customersRepository->findByFirstPaymentCompletedNoInvoice();
    }

    public function getListMaintainersCards()
    {
        return $this->customersRepository->findMaintainersCards();
    }

    public function getUserByEmailPassword($s_username, $s_password)
    {
        return $this->customersRepository->getUserByEmailPassword($s_username, $s_password);
    }

    public function findByEmail($email)
    {
        return $this->customersRepository->findByCI('email', $email);
    }

    public function findById($id)
    {
        return $this->customersRepository->findOneBy([
            'id' => $id
        ]);
    }

    public function findByTaxCode($taxCode)
    {
        return $this->customersRepository->findByCI('taxCode', $taxCode);
    }

    public function findByDriversLicense($driversLicense)
    {
        return $this->customersRepository->findByCI('driverLicense', $driversLicense);
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

        if (!$card->getAssignable()) {
            return false;
        }

        if (!is_null($isAssigned) && $isAssigned === true) {
            $card->setIsAssigned(true);
            $card->setAssignable(false);
        }

        $customer->setCard($card);

        $this->entityManager->persist($customer);
        $this->entityManager->flush();
    }

    public function removeCard(Customers $customer)
    {
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

    public function removeBonus(CustomersBonus $customerBonus)
    {
        if ($customerBonus->getTotal() == $customerBonus->getResidual()) {

            $this->entityManager->remove($customerBonus);
            $this->entityManager->flush();

            return true;
        }

        return false;
    }

    public function retrieveLatePayers()
    {
        return $this->customersRepository->getLatePayers();
    }

    /**
     * sets the users as disabled and sends them a notification by email
     * the boolean parameters allow the run the method without side effects
     *
     * @param Customers[] $customers
     * @param boolean $persist
     * @param boolean $sendEmails
     */
    public function disableForLatePayment($customers, $persist = true, $sendEmails = true)
    {
        // notification email attachments
        $attachments = [
            'bannerphono.jpg' => __DIR__.'/../../../../../public/images/bannerphono.jpg',
            'barbarabacci.jpg' => __DIR__.'/../../../../../public/images/barbarabacci.jpg'
        ];

        foreach ($customers as $customer) {
            // disable the customer
            $customer->disable();

            $this->logger->log(
                "Disable user ".$customer->getId().": "
                .$customer->getName()." ".$customer->getSurname()."\n"
            );

            if ($persist) {
                $this->entityManager->persist($customer);
                $this->entityManager->flush();
            }

            // send an email notification
            $content = sprintf(
                file_get_contents(__DIR__.'/../../../view/emails/late-payment-it_IT.html'),
                $customer->getName(),
                $customer->getSurname()
            );

            if ($sendEmails) {
                $this->logger->log("Send notification email\n\n");

                $this->emailService->sendEmail(
                    $customer->getEmail(),
                    'SHARENGO - NOTIFICA DI DISABILITAZIONE',
                    $content,
                    $attachments
                );
            }
        }
    }

    /**
     * enables a customer to reserve a car
     *
     * @param Customers $customer,
     * @param boolean $sendEmail
     */
    public function enableCustomer(Customers $customer, $sendEmail)
    {
        $customer->enable();

        $this->entityManager->persist($customer);
        $this->entityManager->flush();

        if ($sendEmail) {
            $this->sendEnabledNotification($customer);
        }
    }

    /**
     * send to a customer an email telling him that he was re-enabled
     *
     * @param Customers $customer
     */
    private function sendEnabledNotification(Customers $customer)
    {
        $content = sprintf(
            file_get_contents(__DIR__.'/../../../view/emails/re-enable-customer-it_IT.html'),
            $customer->getName(),
            $customer->getSurname()
        );

        $attachments = [
            'bannerphono.jpg' => __DIR__.'/../../../../../public/images/bannerphono.jpg',
            'barbarabacci.jpg' => __DIR__.'/../../../../../public/images/barbarabacci.jpg'
        ];

        $this->emailService->sendEmail(
            $customer->getEmail(),
            'SHARENGO - RIABILITAZIONE UTENTE',
            $content,
            $attachments
        );
    }

    /**
     * set the customer paymentAble flag to true
     * reabilitates him to be processed in trips to be payed
     *
     * @var Customers $customer
     */
    public function setCustomerPaymentAble(Customers $customer)
    {
        $customer->setPaymentAble(true);

        $this->entityManager->persist($customer);
        $this->entityManager->flush();
    }
}
