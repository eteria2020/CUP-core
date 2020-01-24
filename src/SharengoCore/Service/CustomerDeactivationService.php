<?php

namespace SharengoCore\Service;

use Cartasi\Entity\Contracts;
use SharengoCore\Entity\BonusPackagePayment;
use SharengoCore\Entity\Customers;
use SharengoCore\Entity\CustomerDeactivation;
use SharengoCore\Entity\ExtraPaymentTries;
use SharengoCore\Entity\Queries\FindCustomerDeactivationById;
use SharengoCore\Entity\Queries\FindActiveCustomerDeactivations;
use SharengoCore\Entity\Queries\FindCustomerDeactivationsToUpdate;
use SharengoCore\Entity\Queries\ShouldActivateCustomer;
use SharengoCore\Entity\Repository\CustomerDeactivationRepository;
use SharengoCore\Entity\SubscriptionPayment;
use SharengoCore\Entity\TripPaymentTries;
use SharengoCore\Entity\Webuser;

use Doctrine\ORM\EntityManager;

class CustomerDeactivationService
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var CustomersService
     */
    private $customerService;

    /**
     * @var CustomerDeactivationRepository
     */
    private $repository;

    /**
     * @param EntityManager $entityManager
     * @param CustomersService $customerService
     */
    public function __construct(
        EntityManager $entityManager,
        CustomersService $customerService,
        CustomerDeactivationRepository $repository
    ) {
        $this->entityManager = $entityManager;
        $this->customerService = $customerService;
        $this->repository = $repository;
    }

    /**
     * @param integer $id
     * @return CustomerDeactivation
     */
    public function getById($id)
    {
        $query = new FindCustomerDeactivationById($this->entityManager, $id);

        return $query();
    }

    /**
     * @param Customers $customer
     * @param string|null $reason
     * @return CustomerDeactivation|CustomerDeactivation[] if a reason if present just one (or none) result is returned
     */
    public function getAllActive(Customers $customer, $reason = null)
    {
        $query = new FindActiveCustomerDeactivations(
            $this->entityManager,
            $customer,
            $reason
        );

        return $query();
    }

    /**
     * @param Customers $customer
     * @param string|null $reason
     * @return bool
     */
    public function hasActiveDeactivations(Customers $customer, $reason = null)
    {
        $allActive = $this->getAllActive($customer, $reason);

        return !empty($allActive);
    }

    /**
     * Create CustomerDeactivation for when Customer hasn't yet payed the
     * first payment
     *
     * @param Customers $customer
     */
    public function deactivateAtRegistration(Customers $customer)
    {
        $this->deactivate(
            $customer,
            CustomerDeactivation::FIRST_PAYMENT_NOT_COMPLETED,
            []
        );
    }

    /**
     * Create CustomerDeactivation for when Customer hasn't yet finished the
     * registration
     *
     * @param Customers $customer
     */
    public function deactivateRegistrationNotCompleted(Customers $customer)
    {
        $this->deactivate(
            $customer,
            CustomerDeactivation::REGISTRATION_NOT_COMPLETED,
            []
        );
    }

    /**
     * Deactivate Customer that failed to pay a TripPayment
     *
     * @param Customers $customer
     * @param TripPaymentTries $tripPaymentTry
     * @param \DateTime|null $startTs
     */
    public function deactivateForTripPaymentTry(
        Customers $customer,
        TripPaymentTries $tripPaymentTry,
        \DateTime $startTs = null
    ) {
        $details = ['trip_payment_try_id' => $tripPaymentTry->getId()];

        $this->deactivate(
            $customer,
            CustomerDeactivation::FAILED_PAYMENT,
            $details,
            $startTs
        );
    }
    
    /**
     * Deactivate Customer that failed to pay a ExtraPayment
     *
     * @param Customers $customer
     * @param ExtraPaymentTries $extraPaymentTry
     * @param \DateTime|null $startTs
     */
    public function deactivateForExtraPaymentTry(
        Customers $customer,
        $extraPaymentTry,
        \DateTime $startTs = null
    ) {
        $details = ['extra_payment_try_id' => $extraPaymentTry->getId()];

        $this->deactivate(
            $customer,
            CustomerDeactivation::FAILED_EXTRA_PAYMENT,
            $details,
            $startTs
        );
    }

    /**
     * Deactivate customer because the valid bonuses are under threshold
     *
     * @param Customers $customer
     * @param $trip
     * @param \DateTime|null $startTs
     */
    public function deactivateForCustomerBonusThreshold(
        Customers $customer,
        $trip,
        \DateTime $startTs = null
    ) {

        $details = ['total_customer_bonus' => $customer->getTotalBonuses()];

        if(!is_null($trip)){
            $details = ['trip_id' => $trip->getId()];
        }

        $this->deactivate(
            $customer,
            CustomerDeactivation::CUSTOMER_BONUS_THRESHOLD,
            $details,
            $startTs
        );
    }

    /**
     * Deactivate Customer that has an invalid driver's license
     *
     * @param Customers $customer
     * @param \DateTime|null $startTs
     */
    public function deactivateForDriversLicense(
        Customers $customer,
        \DateTime $startTs = null
    ) {
        $details = $this->getDriverLicenceDetails($customer);

        $this->deactivate(
            $customer,
            CustomerDeactivation::INVALID_DRIVERS_LICENSE,
            $details,
            $startTs
        );
    }

     /**
     * Deactivate Customer that has an expired driver's license
     *
     * @param Customers $customer
     * @param \DateTime|null $startTs
     */
    public function deactivateForExpiredDriversLicense(
        Customers $customer,
        \DateTime $startTs = null
    ) {
        $details = $this->getDriverLicenceDetails($customer);

        $this->deactivate(
            $customer,
            CustomerDeactivation::EXPIRED_DRIVERS_LICENSE,
            $details,
            $startTs
        );
    }
    
    /**
     * Deactivate Customer that was disabled by Webuser
     *
     * @param Customers $customer
     * @param Webuser $webuser
     * @param string|null $note
     * @param \DateTime|null $startTs
     */
    public function deactivateByWebuser(
        Customers $customer,
        Webuser $webuser,
        $note = null,
        \DateTime $startTs = null
    ) {
        $details = [
            'note' => ($note === null) ? 'Deactivated manually' : $note
        ];

        $this->deactivate(
            $customer,
            CustomerDeactivation::DISABLED_BY_WEBUSER,
            $details,
            $startTs,
            $webuser
        );
    }
    
    /**
     * Deactivate Customer that was disabled by script disable-credit-card
     *
     * @param Customers $customer
     * @param string|null $note
     * @param \DateTime|null $startTs
     */
    public function deactivateByScriptDisableCreditCard(
        Customers $customer
    ) {
        $details = ['details' => 'Deactivated by script disable-credit-card because credit card of customer is expired'];

        $this->deactivate(
            $customer,
            CustomerDeactivation::EXPIRED_CREDIT_CARD,
            $details
        );
    }


    /**
     * @param Customers $customer
     * @param string $reason
     * @param array $details
     * @param \DateTime|null $startTs
     * @param Webuser|null $webuser
     */
    private function deactivate(
        Customers $customer,
        $reason,
        array $details,
        \DateTime $startTs = null,
        Webuser $webuser = null
    ) {
        $customerDeactivation = new CustomerDeactivation(
            $customer,
            $reason,
            $details,
            $startTs,
            $webuser
        );

        $this->entityManager->persist($customerDeactivation);

        // Disable Customer if this CustomerDeactivation is immediate
        if ($customerDeactivation->isEffective()) {
            $customer->disable();
            $this->entityManager->persist($customer);
        }

        $this->entityManager->flush();

        // Reactivate deactivations that are overridden by this one
        $this->reactivateOlder($customerDeactivation);
    }

    /**
     * Close the CustomerDeactivation when the Customer pays the subscription
     *
     * @param CustomerDeactivation $customerDeactivation
     * @param SubscriptionPayment $subscriptionPayment
     * @param \DateTime|null $endTs
     */
    public function reactivateForFirstPayment(
        CustomerDeactivation $customerDeactivation,
        SubscriptionPayment $subscriptionPayment,
        \DateTime $endTs = null
    ) {
        $details = [
            'subscription_payment_id' => $subscriptionPayment->getId()
        ];

        $this->reactivate($customerDeactivation, $details, $endTs);
    }

    /**
     * Close the CustomerDeactivation when the Customer finishes the subscription
     *
     * @param CustomerDeactivation $customerDeactivation
     * @param SubscriptionPayment $subscriptionPayment
     * @param \DateTime|null $endTs
     */
    public function reactivateForRegistrationCompleted(
        CustomerDeactivation $customerDeactivation,
        \DateTime $endTs = null
    ) {
        $details = [];

        $this->reactivate($customerDeactivation, $details, $endTs);
    }

    /**
     * Close the CustomerDeactivation when a TripPayment is successfully
     * completed
     *
     * @param CustomerDeactivation $customerDeactivation
     * @param TripPaymentTries $tripPaymentTry
     * @param \DateTime|null $endTs
     * @param Webuser|null $webuser
     */
    public function reactivateForTripPaymentTry(
        CustomerDeactivation $customerDeactivation,
        TripPaymentTries $tripPaymentTry,
        \DateTime $endTs = null,
        Webuser $webuser = null
    ) {
        $details = ['trip_payment_try_id' => $tripPaymentTry->getId()];
        if ($webuser instanceof Webuser) {
            $details['note'] = 'Reactivated manually';
        }

        $this->reactivate($customerDeactivation, $details, $endTs, $webuser);
    }

    /**
     * Close the CustomerDeactivation when a ExtraPayment is successfully
     * completed
     *
     * @param CustomerDeactivation $customerDeactivation
     * @param ExtraPaymentTries $extraPaymentTry
     * @param \DateTime|null $endTs
     * @param Webuser|null $webuser
     */
    public function reactivateForExtraPaymentTry(
        CustomerDeactivation $customerDeactivation,
        ExtraPaymentTries $extraPaymentTry,
        \DateTime $endTs = null,
        Webuser $webuser = null
    ) {
        $details = ['extra_payment_try_id' => $extraPaymentTry->getId()];
        if ($webuser instanceof Webuser) {
            $details['note'] = 'Reactivated manually';
        }

        $this->reactivate($customerDeactivation, $details, $endTs, $webuser);
    }

    /**
     * Reactivates all CustomerDeactivations with reason FAILED_PAYMENT for a
     * specific Customer
     *
     * @param Customers $customer
     * @param TripPaymentTries $tripPaymentTry
     * @param Webuser|null $webuser
     * @param \DateTime|null $endTs
     */
    public function reactivateCustomerForTripPaymentTry(
        Customers $customer,
        TripPaymentTries $tripPaymentTry,
        Webuser $webuser = null,
        \DateTime $endTs = null
    ) {
        $query = new FindActiveCustomerDeactivations(
            $this->entityManager,
            $customer,
            CustomerDeactivation::FAILED_PAYMENT
        );

        $deactivation = $query();
        if ($deactivation instanceof CustomerDeactivation) {
            $this->reactivateForTripPaymentTry(
                $deactivation,
                $tripPaymentTry,
                $endTs,
                $webuser
            );
        }
    }

    /**
     * Remove all deactivations with reason FAILED_PAYMENT. 
     * If this is the only reason, the customer will be enabled, instead stay disabled.
     * @param Customers $customer
     * @param Webuser $webuser
     * @param \DateTime $endTs
     * @param bool $sendEmail
     */
    public function reactivateCustomerForFailedPayment(
        Customers $customer,
        Webuser $webuser = null,
        \DateTime $endTs = null,
        $sendEmail = true
    ) {
        $customerDeactivations = $this->getAllActive($customer, CustomerDeactivation::FAILED_PAYMENT);
        $details = [
            'note' => 'Reactivated from manually payment'
        ];

        if (is_array($customerDeactivations)) {
            foreach($customerDeactivations as $deactivation) {
                $this->reactivate($deactivation, $details, $endTs, $webuser, $sendEmail);
            }
        } else {
            if(!empty($customerDeactivations)){
                $this->reactivate($customerDeactivations, $details, $endTs, $webuser, $sendEmail);
            }
        }

    }

    /**
     * Close the CustomerDeactivation when a BonusPackagePayment is successfully
     * completed
     *
     * @param CustomerDeactivation $customerDeactivation
     * @param BonusPackagePayment $bonusPackagePayment
     * @param \DateTime|null $endTs
     */
    public function reactivateForBonusPayment(
        CustomerDeactivation $customerDeactivation,
        BonusPackagePayment $bonusPackagePayment,
        \DateTime $endTs = null
    ) {
        $details = ['bonus_package_payment_id' => $bonusPackagePayment->getId()];

        $this->reactivate($customerDeactivation, $details, $endTs);
    }

    /**
     * Close the CustomerDeactivation when the driver's license is verified
     *
     * @param CustomerDeactivation $customerDeactivation
     * @param \DateTime|null $endTs
     * @param Webuser $webuser
     */
    public function reactivateForDriversLicense(
        CustomerDeactivation $customerDeactivation,
        \DateTime $endTs = null,
        Webuser $webuser = null
    ) {
        $details = $this->getDriverLicenceDetails($customerDeactivation->getCustomer());
        $this->reactivate($customerDeactivation, $details, $endTs, $webuser, false);
    }

    /**
     * Close the CustomerDeactivation for customer bonus under threshold
     *
     * @param Customers $customer
     * @param \DateTime|null $endTs
     * @param Webuser|null $webuser
     * @param bool $sendEmail
     */
    public function reactivateForCustomerBonusThreshold(
        Customers $customer,
        \DateTime $endTs = null,
        Webuser $webuser = null,
        $sendEmail = true
    ) {
        $customerDeactivations = $this->getAllActive($customer, CustomerDeactivation::CUSTOMER_BONUS_THRESHOLD);
        $details = [
            'note' => 'Customer bonus total ' . $customer->getTotalBonuses()
        ];

        if (is_array($customerDeactivations)) {
            foreach($customerDeactivations as $deactivation) {
                $this->reactivate($deactivation, $details, $endTs, $webuser, $sendEmail);
            }
        } else {
            if(!empty($customerDeactivations)){
                $this->reactivate($customerDeactivations, $details, $endTs, $webuser, $sendEmail);
            }
        }
    }

    /**
     * Reactivates all CustomerDeactivations with reason INVALID_DRIVERS_LICENSE
     * for a specific Customer
     *
     * @param Customers $customer
     * @param \DateTime|null $endTs
     * @param Webuser $webuser
     */
    public function reactivateCustomerForDriversLicense(
        Customers $customer,
        \DateTime $endTs = null,
        Webuser $webuser = null
    ) {
        $query = new FindActiveCustomerDeactivations(
            $this->entityManager,
            $customer,
            CustomerDeactivation::INVALID_DRIVERS_LICENSE
        );

        $deactivation = $query();
        if ($deactivation instanceof CustomerDeactivation) {
            $this->reactivateForDriversLicense($deactivation, $endTs, $webuser);
        }

        //Reactivates all CustomerDeactivations with reason EXPIRED_DRIVERS_LICENSE
        $queryExpiredDriverLicense = new FindActiveCustomerDeactivations(
            $this->entityManager,
            $customer,
            CustomerDeactivation::EXPIRED_DRIVERS_LICENSE
        );
        $deactivationExpiredDriverLicense = $queryExpiredDriverLicense();
        if ($deactivationExpiredDriverLicense instanceof CustomerDeactivation) {
            $this->reactivateForDriversLicense($deactivationExpiredDriverLicense, $endTs, $webuser);
        }
    }

    /**
     * Reactivates Customer with only FIRST_PAYMENT_NOT_COMPLETED from admin
     *
     * @param Customers $customer
     * @param Webuser $webuser
     */
    public function reactivateCustomerForFirstPaymentFromAdmin(
    Customers $customer, Webuser $webuser
    ) {

        $c_d = $this->getAllActive($customer);
        if (count($c_d) == 1) {
            if ($c_d[0]->getReason() == CustomerDeactivation::FIRST_PAYMENT_NOT_COMPLETED) {
                $this->reactivateByWebuser($c_d[0], $webuser, '', new \Datetime());
            }
        } else {
            foreach ($c_d as $cd) {
                if ($cd->getReason() == CustomerDeactivation::FIRST_PAYMENT_NOT_COMPLETED) {
                    $this->reactivate($cd, [], new \Datetime(), $webuser);
                }
            }
        }
    }

    /**
     * Reactivates Customer with only FAILED_EXTRA_PAYMENT from admin after passed payed extra
     *
     * @param Customers $customer
     * @throws \Exception
     */
    public function reactivateCustomerForExtraPayed(Customers $customer) {
        $c_d = $this->getAllActive($customer);
        foreach ($c_d as $cd) {
            if ($cd->getReason() == CustomerDeactivation::FAILED_EXTRA_PAYMENT) {
                $this->reactivate($cd, [], new \Datetime());
            }
        }
    }

    /**
     * Close the CustomerDeactivation when the Webuser removes it
     *
     * @param CustomerDeactivation $customerDeactivation
     * @param Webuser $webuser
     * @param string|null $note
     * @param \DateTime|null $endTs
     */
    public function reactivateByWebuser(
        CustomerDeactivation $customerDeactivation,
        Webuser $webuser,
        $note = null,
        \DateTime $endTs = null
    ) {
        $details = [
            'note' => ($note === null) ? 'Reactivated manually' : $note
        ];

        $this->reactivate($customerDeactivation, $details, $endTs, $webuser);
    }

    /**
     * Close all the CustomerDeactivations of a Customer
     *
     * @param CustomerDeactivation $customerDeactivation
     * @param Webuser $webuser
     * @param string|null $note
     * @param \DateTime|null $endTs
     */
    public function reactivateCustomer(
        Customers $customer,
        Webuser $webuser,
        $note = null,
        \DateTime $endTs = null
    ) {
        $deactivations = $this->getAllActive($customer);
        foreach ($deactivations as $deactivation) {
            $this->reactivateByWebuser($deactivation, $webuser, $note, $endTs);
        }
    }

    /**
     * Close the CustomerDeactivation and enable Customer if necessary
     *
     * @param CustomerDeactivation $customerDeactivation
     * @param array $details
     * @param \DateTime|null $endTs
     * @param Webuser|null $webuser
     * @param bool $sendEmail
     */
    private function reactivate(
        CustomerDeactivation $customerDeactivation,
        array $details,
        \DateTime $endTs = null,
        Webuser $webuser = null,
        $sendEmail = true
    ) {
        $customerDeactivation->reactivate($details, $endTs, $webuser);
        $this->entityManager->persist($customerDeactivation);

        // If it was the last active CustomerDeactivation for the Customer
        // and this one is deactivated immediately, enable Customer
        $customer = $customerDeactivation->getCustomer();
        if ($this->shouldActivateCustomer($customer, $customerDeactivation)) {
            $this->customerService->enableCustomer($customer, $sendEmail);
            $this->entityManager->persist($customer);
        }
        $this->entityManager->flush();
    }

    /**
     * @param Customers $customer
     * @param CustomerDeactivation|null $customerDeactivation this deactivation
     *     is ignored in the check if not null
     * @return boolean wether there are no active CustomerDeactivations for the
     *     Customer
     */
    private function shouldActivateCustomer(
        Customers $customer,
        CustomerDeactivation $customerDeactivation = null
    ) {
        $query = new ShouldActivateCustomer(
            $this->entityManager,
            $customer,
            $customerDeactivation
        );

        return $query();
    }

    /**
     * @param Customers $customer
     * @return array
     */
    private function getDriverLicenceDetails(Customers $customer)
    {
        return [
            'driver_license' => $customer->getDriverLicense(),
            'driver_license_categories' => $customer->getDriverLicenseCategories(),
            'driver_license_authority' => $customer->getDriverLicenseAuthority(),
            'driver_license_country' => $customer->getDriverLicenseCountry(),
            'driver_license_release_date' => (is_null($customer->getDriverLicenseReleaseDate())) ? null : $customer->getDriverLicenseReleaseDate()->format('Y-m-d H:i:s'),
            'driver_license_expire' => (is_null($customer->getDriverLicenseExpire())) ? null : $customer->getDriverLicenseExpire()->format('Y-m-d H:i:s'),
            'driver_license_name' => $customer->getDriverLicenseName(),
            'driver_license_surname' => $customer->getDriverLicenseSurname()
        ];
    }

    /**
     * Find CustomerDeactivations that are overridden by new one and reactivate
     * them
     *
     * @param CustomerDeactivation $customerDeactivation
     */
    private function reactivateOlder(CustomerDeactivation $customerDeactivation)
    {
        $query = new FindCustomerDeactivationsToUpdate(
            $this->entityManager,
            $customerDeactivation
        );

        $customerDeactivations = $query();

        foreach ($customerDeactivations as $value) {
            $this->reactivate(
                $value,
                ['updated_deactivation' => $customerDeactivation->getId()]
            );
        }
    }

    /**
     * Close the CustomerDeactivation when the Customer change expired credit card with a new one
     *
     * @param CustomerDeactivation $customerDeactivation
     * @param Contracts $contract
     * @param \DateTime|null $endTs
     */
    public function reactivateForExpiredCreditCard(
        CustomerDeactivation $customerDeactivation,
        Contracts $contract,
        \DateTime $endTs = null
    ) {
        $details = [
            'new_contract_id' => $contract->getId()
        ];

        $this->reactivate($customerDeactivation, $details, $endTs);
    }
    
    public function findByIdOrderByInsertedTs($customer){
        $deactivation = $this->repository->findbyIdOrderByInsertedTs($customer);
        if(isset($deactivation[0])){
            return $deactivation[0];
        }
        return null;
    }
}
