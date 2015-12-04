<?php

namespace SharengoCore\Service;

use SharengoCore\Entity\BonusPackagePayment;
use SharengoCore\Entity\Customers;
use SharengoCore\Entity\CustomerDeactivation;
use SharengoCore\Entity\Queries\FindCustomerDeactivationById;
use SharengoCore\Entity\Queries\FindCustomerDeactivations;
use SharengoCore\Entity\Queries\FindCustomerDeactivationsToUpdate;
use SharengoCore\Entity\Queries\ShouldActivateCustomer;
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
     * @param EntityManager $entityManager
     * @param CustomersService $customerService
     */
    public function __construct(
        EntityManager $entityManager,
        CustomersService $customerService
    ) {
        $this->entityManager = $entityManager;
        $this->customerService = $customerService;
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
     * @return CustomerDeactivation[]
     */
    public function getAll(Customers $customer, $reason = null)
    {
        $query = new FindCustomerDeactivations(
            $this->entityManager,
            $customer,
            $reason
        );

        return $query();
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
     * Close the CustomerDeactivation when a TripPayment is successfully
     * completed
     *
     * @param CustomerDeactivation $customerDeactivation
     * @param TripPaymentTries $tripPaymentTry
     * @param \DateTime|null $endTs
     */
    public function reactivateForTripPaymentTry(
        CustomerDeactivation $customerDeactivation,
        TripPaymentTries $tripPaymentTry,
        \DateTime $endTs = null
    ) {
        $details = ['trip_payment_try_id' => $tripPaymentTry->getId()];

        $this->reactivate($customerDeactivation, $details, $endTs);
    }

    /**
     * Close the CustomerDeactivation when a TripPayment is successfully
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
     */
    public function reactivateForDriversLicense(
        CustomerDeactivation $customerDeactivation,
        \DateTime $endTs = null
    ) {
        $details = $this->getDriverLicenceDetails($customer);

        $this->reactivate($customerDeactivation, $details, $endTs);
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
        $deactivations = $this->getCustomerDeactivations($customer);
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
     */
    private function reactivate(
        CustomerDeactivation $customerDeactivation,
        array $details,
        \DateTime $endTs = null,
        Webuser $webuser = null
    ) {
        $customerDeactivation->reactivate($details, $endTs, $webuser);
        $this->entityManager->persist($customerDeactivation);
        $this->entityManager->flush();

        // If it was the last active CustomerDeactivation for the Customer
        // and this one is deactivated immediatly, enable Customer
        $customer = $customerDeactivation->getCustomer();
        if ($this->shouldActivateCustomer($customer)) {
            $this->customerService->enableCustomer($customer, true);
            $this->entityManager->persist($customer);
            $this->entityManager->flush();
        }
    }

    /**
     * @param Customers $customer
     * @return boolean wether there are no active CustomerDeactivations for the
     *     Customer
     */
    private function shouldActivateCustomer(Customers $customer)
    {
        $query = new ShouldActivateCustomer($this->entityManager, $customer);

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
            'driver_license_release_date' => $customer->getDriverLicenseReleaseDate()->format('Y-m-d H:i:s'),
            'driver_license_expire' => $customer->getDriverLicenseExpire()->format('Y-m-d H:i:s'),
            'driver_license_name' => $customer->getDriverLicenseName(),
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
}
