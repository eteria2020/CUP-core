<?php

namespace SharengoCore\Service;

use SharengoCore\Entity\Customers;
use SharengoCore\Entity\CustomerDeactivation;
use SharengoCore\Entity\Queries\ShouldActivateCustomer;
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
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
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
        $details = [
            'trip_payment_try_id' => $tripPaymentTry->getId()
        ];

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
     * @param string $note
     * @param \DateTime|null $startTs
     */
    public function deactivateByWebuser(
        Customers $customer,
        Webuser $webuser,
        $note = '',
        \DateTime $startTs = null
    ) {
        $details = [
            'note' => $note
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
    }

    /**
     * Close the CustomerDeactivation when the Customer pays the subscription
     *
     * @param CustomerDeactivation $customerDeactivation
     * @param SubscriptionPayment $subscriptionPayment
     * @param \DateTime $endTs
     */
    public function activateForFirstPayment(
        CustomerDeactivation $customerDeactivation,
        SubscriptionPayment $subscriptionPayment,
        \DateTime $endTs
    ) {
        $details = [
            'subscription_payment_id' => $subscriptionPayment->getId()
        ];

        $this->activate($customerDeactivation, $details, $endTs);
    }

    /**
     * Close the CustomerDeactivation when a TripPayment is successfully
     * completed
     *
     * @param CustomerDeactivation $customerDeactivation
     * @param \DateTime|null $endTs
     */
    public function activateForTripPaymentTry(
        CustomerDeactivation $customerDeactivation,
        TripPaymentTries $tripPaymentTry,
        \DateTime $endTs = null
    ) {
        $details = [
            'trip_payment_try_id' => $tripPaymentTry->getId()
        ];

        $this->activate($customerDeactivation, $details, $endTs);
    }

    /**
     * Close the CustomerDeactivation when the driver's license is verified
     *
     * @param CustomerDeactivation $customerDeactivation
     * @param \DateTime|null $endTs
     */
    public function activateForDriversLicense(
        CustomerDeactivation $customerDeactivation,
        \DateTime $endTs = null
    ) {
        $details = $this->getDriverLicenceDetails($customer);

        $this->activate($customerDeactivation, $details, $endTs);
    }

    /**
     * Close the CustomerDeactivation when the Webuser sets the Customer active
     *
     * @param CustomerDeactivation $customerDeactivation
     * @param Webuser $webuser
     * @param string $note
     * @param \DateTime|null $endTs
     */
    public function activateByWebuser(
        CustomerDeactivation $customerDeactivation,
        Webuser $webuser,
        $note = '',
        \DateTime $endTs = null
    ) {
        $details = [
            'note' => $note
        ];

        $this->activate($customerDeactivation, $details, $endTs, $webuser);
    }

    /**
     * Close the CustomerDeactivation and enable Customer if necessary
     *
     * @param CustomerDeactivation $customerDeactivation
     * @param array $details
     * @param \DateTime|null $endTs
     * @param Webuser|null $webuser
     */
    private function activate(
        CustomerDeactivation $customerDeactivation,
        array $details,
        \DateTime $endTs = null,
        Webuser $webuser = null
    ) {
        $customerDeactivation->activate($details, $endTs, $webuser);
        $this->entityManager->persist($customerDeactivation);
        $this->entityManager->flush();

        // If it was the last active CustomerDeactivation for the Customer
        // and this one is deactivated immediatly, enable Customer
        if ($this->shouldActivateCustomer()) {
            $customer = $customerDeactivation->getCustomer();
            $customer->activate();
            $this->entityManager->persist($customer);
            $this->entityManager->flush();
        }
    }

    /**
     * @param Customers $customer
     * @return boolean wether there are no active CustomerDeactivations for the
     *     Customer
     */
    private function shouldActivateCustomer(Customers $customer) {
        $query = new ShouldActivateCustomer(
            $this->entityManager,
            $customer
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
            'driver_license_release_date' => $customer->getDriverLicenseReleaseDate()->format('Y-m-d H:i:s'),
            'driver_license_expire' => $customer->getDriverLicenseExpire()->format('Y-m-d H:i:s'),
            'driver_license_name' => $customer->getDriverLicenseName(),
        ];
    }
}
