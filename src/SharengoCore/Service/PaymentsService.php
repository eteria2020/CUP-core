<?php

namespace SharengoCore\Service;

use Cartasi\Service\CartasiCustomerPayments;
use Cartasi\Service\CartasiContractsService;
use SharengoCore\Entity\Customers;
use SharengoCore\Entity\TripPayments;
use SharengoCore\Entity\TripPaymentTries;

use Doctrine\ORM\EntityManager;
use Zend\EventManager\EventManager;

class PaymentsService
{
    /**
     * @var CartasiCustomerPayments
     */
    private $cartasiCustomerPayments;

    /**
     * @var CartasiContractService
     */
    private $cartasiContractService;

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var EmailService
     */
    private $emailService;

    /**
     * @var EventManager
     */
    private $eventManager;

    /**
     * @var string
     */
    private $url;

    /**
     * @var boolean
     */
    private $avoidCartasi = true;

    /**
     * @var boolean
     */
    private $avoidEmail = true;

    /**
     * @var boolean
     */
    private $avoidPersistance = true;

    /**
     * @param CartasiCustomerPayments $cartasiCustomerPayments
     * @param CartasiContractsService $cartasiContractService
     * @param EntityManager $entityManager
     * @param EmailService $emailService
     * @param string $url
     */
    public function __construct(
        CartasiCustomerPayments $cartasiCustomerPayments,
        CartasiContractsService $cartasiContractService,
        EntityManager $entityManager,
        EmailService $emailService,
        EventManager $eventManager,
        $url
    ) {
        $this->cartasiCustomerPayments = $cartasiCustomerPayments;
        $this->cartasiContractService = $cartasiContractService;
        $this->entityManager = $entityManager;
        $this->emailService = $emailService;
        $this->eventManager = $eventManager;
        $this->url = $url;
    }

    /**
     * tries to pay the tripPayment, checking first if the trip ca be payed and
     * otherwise sending a payment request to the customer
     *
     * @param TripPayments $tripPayment
     */
    public function tryPayment(
        TripPayments $tripPayment,
        $avoidEmail = false,
        $avoidCartasi = false,
        $avoidPersistance = false
    ) {
        $this->avoidEmail = $avoidEmail;
        $this->avoidCartasi = $avoidCartasi;
        $this->avoidPersistance = $avoidPersistance;

        $trip = $tripPayment->getTrip();
        $customer = $trip->getCustomer();

        if ($this->cartasiContractService->hasCartasiContract($customer)) {
            $this->tryCustomerTripPayment(
                $customer,
                $tripPayment
            );
        } else {
            $this->disableCustomerForPayment($customer);

            // enable hooks on the event that the customer doesn't have a valid contract
            $this->eventManager->trigger('notifyCustomerPay', $this, [
                'customer' => $customer,
                'tripPayment' => $tripPayment
            ]);
        }
    }

    /**
     * @var Customers $customer
     */
    private function disableCustomerForPayment(Customers $customer)
    {
        $customer->setPaymentAble(false);

        $this->entityManager->persist($customer);

        if (!$this->avoidPersistance) {
            $this->entityManager->flush();
        }
    }

    /**
     * tries to pay the trip amount
     * writes in database a record in the trip_payment_tries table
     *
     * @param TripPayments $tripPayment
     * @param boolean $avoidEmail
     * @param boolean $avoidCartasi
     * @param boolean $avoidPersistance
     * @param boolean $avoidDisableUser
     * @return CartasiResponse
     */
    public function tryTripPayment(
        TripPayments $tripPayment,
        $avoidEmail = false,
        $avoidCartasi = false,
        $avoidPersistance = false,
        $avoidDisableUser = false
    ) {
        $this->avoidEmail = $avoidEmail;
        $this->avoidCartasi = $avoidCartasi;
        $this->avoidPersistance = $avoidPersistance;

        $customer = $tripPayment->getCustomer();

        return $this->tryCustomerTripPayment(
            $customer,
            $tripPayment,
            $avoidDisableUser
        );
    }

    /**
     * tries to pay the trip amount
     * writes in database a record in the trip_payment_tries table
     *
     * @param Customers $customer
     * @param TripPayments $tripPayment
     * @param boolean $avoidDisableUser
     * @return CartasiResponse
     */
    private function tryCustomerTripPayment(
        Customers $customer,
        TripPayments $tripPayment,
        $avoidDisableUser = false
    ) {
        $response = $this->cartasiCustomerPayments->sendPaymentRequest(
            $customer,
            $tripPayment->getTotalCost(),
            $this->avoidCartasi
        );

        $this->entityManager->getConnection()->beginTransaction();

        try {
            if ($response->getCompletedCorrectly()) {
                $this->markTripAsPayed($tripPayment);
            } else {
                $this->unpayableConsequences($customer, $tripPayment, $avoidDisableUser);
            }

            $tripPaymentTry = new TripPaymentTries(
                $tripPayment,
                $response->getOutcome(),
                $response->getTransaction()
            );

            $this->entityManager->persist($tripPaymentTry);
            $this->entityManager->flush();

            if (!$this->avoidPersistance) {
                $this->entityManager->getConnection()->commit();
            } else {
                $this->entityManager->getConnection()->rollback();
            }
        } catch (\Exception $e) {
            $this->entityManager->getConnection()->rollback();
            throw $e;
        }

        return $response;
    }

    /**
     * @param TripsPayments $tripPayment
     */
    private function markTripAsPayed(TripPayments $tripPayment)
    {
        $tripPayment->setPayedCorrectly();

        $this->entityManager->persist($tripPayment);
        $this->entityManager->flush();
    }

    /**
     * If the payment of a trip does not complete correctly we:
     * - disable the customer
     * - trip payment set as not payed
     * - send mail to notify customer
     *
     * @param Customers $customer
     * @param TripPayments $tripPayment
     * @param boolean $avoidDisableUser
     */
    private function unpayableConsequences(
        Customers $customer,
        TripPayments $tripPayment,
        $avoidDisableUser
    ) {
        // disable the customer
        if (!$avoidDisableUser) {
            $customer->disable();
        }
        $customer->setPaymentAble(false);

        $this->entityManager->persist($customer);

        // set the trip payment as wrong payment
        $tripPayment->setWrongPayment();

        $this->entityManager->persist($tripPayment);
        $this->entityManager->flush();

        // other unpayable consequences not mentionable here for respect of the childrens
        $this->eventManager->trigger('wrongTripPayment', $this, [
            'customer' => $customer,
            'tripPayment' => $tripPayment
        ]);
    }
}
