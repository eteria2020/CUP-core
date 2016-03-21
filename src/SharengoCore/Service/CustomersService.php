<?php

namespace SharengoCore\Service;

use SharengoCore\Entity\Customers;
use SharengoCore\Entity\CustomersBonus;
use SharengoCore\Entity\PromoCodes;
use SharengoCore\Entity\Repository\CustomersBonusRepository;
use SharengoCore\Entity\Cards;
use SharengoCore\Service\DatatableService;
use SharengoCore\Service\SimpleLoggerService as Logger;
use SharengoCore\Service\TripPaymentsService;
use SharengoCore\Exception\BonusAssignmentException;

use Cartasi\Service\CartasiContractsService;

use Zend\Authentication\AuthenticationService as UserService;
use Doctrine\ORM\EntityManager;
use Zend\Mvc\I18n\Translator;


class CustomersService implements ValidatorServiceInterface
{
    private $validatorEmail;

    private $validatorTaxCode;

    private $entityManager;

    private $customersRepository;

    /**
     * @var CustomersBonusRepository
     */
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
     * @var CartasiContractsService
     */
    private $cartasiContractsService;

    /**
     * @var TripPaymentsService
     */
    private $tripPaymentsService;

    /**
     * @var string website base url
     */
    private $url;

    /**
     * @var Translator
     */
    private $translator;

    /**
     * @param EntityManager $entityManager
     * @param UserService $userService
     * @param DatatableService $datatableService
     * @param CardsService $cardsService
     * @param EmailService $emailService
     * @param Logger $logger
     * @param CartasiContractsService $cartasiContractsService
     * @param TripPaymentsService $tripPaymentsService
     * @param string $url
     */
    public function __construct(
        EntityManager $entityManager,
        UserService $userService,
        DatatableService $datatableService,
        CardsService $cardsService,
        EmailService $emailService,
        Translator $translator,
        Logger $logger,
        CartasiContractsService $cartasiContractsService,
        TripPaymentsService $tripPaymentsService,
        $url
    ) {
        $this->entityManager = $entityManager;
        $this->customersRepository = $this->entityManager->getRepository('\SharengoCore\Entity\Customers');
        $this->customersBonusRepository = $this->entityManager->getRepository('\SharengoCore\Entity\CustomersBonus');
        $this->userService = $userService;
        $this->datatableService = $datatableService;
        $this->cardsService = $cardsService;
        $this->emailService = $emailService;
        $this->translator = $translator;
        $this->logger = $logger;
        $this->cartasiContractsService = $cartasiContractsService;
        $this->tripPaymentsService = $tripPaymentsService;
        $this->url = $url;
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

    public function getDataDataTable(array $as_filters = [], $count = false)
    {
        $customers = $this->datatableService->getData('Customers', $as_filters, $count);

        if ($count) {
            return $customers;
        }

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
                    'registration'        => $customer->getRegistrationCompleted() ? $this->translator->translate('Completata') : $this->translator->translate('Non Completata'),
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

        $this->entityManager->persist($customer);
        $this->entityManager->flush();

        $card->setIsAssigned(false);

        $this->entityManager->persist($card);
        $this->entityManager->flush();
    }

    public function checkUsedPromoCode(Customers $customers, PromoCodes $promoCode)
    {
        return $this->customersBonusRepository->checkUsedPromoCode($customers, $promoCode);
    }

    public function validatePromoCode(Customers $customer, PromoCodes $promoCode)
    {
        if (is_null($promoCode)) {
            throw new BonusAssignmentException($this->translator->translate('Codice promo non valido.'));
        }

        if ($this->checkUsedPromoCode($customer, $promoCode)) {
            throw new BonusAssignmentException($this->translator->translate('Codice promo già associato a questo account.'));
        }
    }

    public function addBonusFromPromoCodeFromWebuser(Customers $customer, PromoCodes $promoCode = null)
    {
        $this->validatePromoCode($customer, $promoCode);

        $customerBonus = CustomersBonus::createFromPromoCode($promoCode);

        $this->addBonusFromWebUser($customer, $customerBonus);
    }

    public function addBonusFromPromoCode(Customers $customer, PromoCodes $promoCode = null)
    {
        $this->validatePromoCode($customer, $promoCode);

        if ($promoCode->getPromocodesinfo()->changesSubscriptionCost()) {
            throw new BonusAssignmentException($this->translator->translate('Codice promo non valido.'));
        }

        $customerBonus = CustomersBonus::createFromPromoCode($promoCode);

        $this->addBonus($customer, $customerBonus);
    }

    public function addBonusFromWebUser(Customers $customer, CustomersBonus $bonus)
    {
        $bonus->setType('promo');
        $bonus->setResidual($bonus->getTotal());
        $bonus->setInsertTs(date_create());
        $bonus->setWebuser($this->userService->getIdentity());

        $this->addBonus($customer, $bonus);
    }

    public function removeBonus(CustomersBonus $customerBonus)
    {
        if ($customerBonus->canBeDeleted()) {
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
            'bannerphono.jpg' => $this->url . '/assets-modules/sharengo-core/images/bannerphono.jpg',
            'barbarabacci.jpg' => $this->url . '/assets-modules/sharengo-core/images/barbarabacci.jpg'
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
                    $this->translator->translate('SHARENGO - NOTIFICA DI DISABILITAZIONE'),
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
     * enables a customer to pay with Cartasi
     *
     * @param Customers $customer
     */
    public function enableCustomerPayment(Customers $customer)
    {
        $customer->setPaymentAble(true);

        $this->entityManager->persist($customer);
        $this->entityManager->flush();
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
            'bannerphono.jpg' => $this->url . '/assets-modules/sharengo-core/images/bannerphono.jpg',
            'barbarabacci.jpg' => $this->url . '/assets-modules/sharengo-core/images/barbarabacci.jpg'
        ];

        $this->emailService->sendEmail(
            $customer->getEmail(),
            $this->translator->translate('SHARENGO - RIABILITAZIONE UTENTE'),
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

    /**
     * @param Customers $customer
     * @return boolean
     */
    public function isFirstTripManualPaymentNeeded(Customers $customer)
    {
        $cartasiCompletedFirstPayment = $customer->getFirstPaymentCompleted();
        if ($cartasiCompletedFirstPayment) {
            if ($this->cartasiContractsService->getCartasiContract($customer) === null &&
            $this->tripPaymentsService->getFirstTripPaymentNotPayedByCustomer($customer) !== null) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param Customers $customer
     * @return string
     */
    public function getExportDataForCustomer($customer)
    {
        $vat = $customer->getVat();
        $vat = str_replace(";", " ", $vat);
        $vat = str_replace("it", "", strtolower($vat));

        $cardCode = $customer->getCard() instanceof Cards ?
            $customer->getCard()->getCode() :
            '';

        /**
         * Every element is in a row
         * The first value in the comments for each row is the code number
         * The second value is the maximum length of that element
         */
        $registry = [
            "GEN", // 10 - max 3
            $customer->getId(), // 41 - max 25
            $cardCode, //50 - max 15
            $vat, // 60 - max 25
            empty($vat) ? 0 : 1, // 61 - max 1
            empty($vat) ? 1 : 0, // 358 - max 1
            str_replace(";", " ", $customer->getTaxCode()), // 70 - max 25
            empty($vat) ? 3 : 2, // 80 - max 1
            $this->truncateIfLonger(str_replace(";", " ", $customer->getSurname()), 30), // 90 - max 30
            $this->truncateIfLonger(str_replace(";", " ", $customer->getName()), 30), // 95 - max 30
            $this->truncateIfLonger(str_replace(";", " ", $customer->getAddress()), 35), // 100 - max 35
            "", // 105 - max 35
            $this->truncateIfLonger(str_replace(";", " ", $customer->getPhone()), 20), // 160 - max 20
            $this->truncateIfLonger(str_replace(";", " ", $customer->getMobile()), 20), // 170 - max 20
            str_replace(";", " ", $customer->getZipCode()), // 110 - max 7
            $this->truncateIfLonger(str_replace(";", " ", $customer->getTown()), 25), // 120 - max 25
            $customer->getBirthProvince(), // 130 - max 2
            str_replace(";", " ", $customer->getBirthCountry()), // 140 - max 3
            $this->truncateIfLonger(str_replace(";", " ", $customer->getSurname()), 25), // 230 - max 25
            $this->truncateIfLonger(str_replace(";", " ", $customer->getName()), 20), // 231 - max 20
            $this->truncateIfLonger(str_replace(";", " ", $customer->getBirthTown()), 25), // 232 - max 25
            $customer->getBirthProvince(), // 233 - max 2
            $customer->getBirthDate()->format("d/m/Y"), // 234 - max 10
            $customer->getGender() == 'male' ? 'M' : 'F', // 235 - max 1
            $customer->getBirthCountry(), // 236 - max 3
            "C01", // 240 - max 6
            "200", // 330 - max 6
            "CC001" // 581 - max 25
        ];
        return implode(";", $registry);
    }

    /**
     * Returns the same string if it is shorter that the specified length,
     * returns the truncated string if it is longer
     *
     * @param string $string string to truncate
     * @param integer $length maximum length
     */
    private function truncateIfLonger($string, $length)
    {
        if (empty($string)) {
            return '';
        }
        return substr($string, 0, $length);
    }

    /**
     * Returns true if the user needs to sign the foreign friver's license form,
     * also if he did it already
     *
     * @param Customers $customer
     * @return bool
     */
    public function customerNeedsToAcceptDriversLicenseForm(Customers $customer)
    {
        return $customer->hasForeignDriverLicense();
    }

    /**
     * Returns true if the user already uploaded a foreign drivers license
     *
     * @param Customers $customer
     * @return bool
     */
    public function customerHasAcceptedDriversLicenseForm(Customers $customer)
    {
        return count($customer->getForeignDriversLicenseUploads()) > 0;
    }

    /**
     * @param string $hash
     * @return Customers
     */
    public function getUserFromHash($hash)
    {
        return $this->customersRepository->findOneBy([
            'hash' => $hash
        ]);
    }
}
