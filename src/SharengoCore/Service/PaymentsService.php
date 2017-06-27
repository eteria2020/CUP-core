<?php

namespace SharengoCore\Service;

use Cartasi\Service\CartasiCustomerPaymentsInterface;
use Cartasi\Service\CartasiContractsService;
use SharengoCore\Service\TripPaymentTriesService;
use SharengoCore\Entity\Customers;
use SharengoCore\Entity\TripPayments;
use SharengoCore\Entity\Webuser;
use SharengoCore\Entity\TripPaymentTries;

use Doctrine\ORM\EntityManager;
use Zend\EventManager\EventManager;

class PaymentsService
{
    /**
     * @var CartasiCustomerPaymentsInterface
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
     * @var TripPaymentTriesService
     */
    private $tripPaymentTriesService;

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
     * @var CustomerDeactivationService
     */
    private $deactivationService;

    /**
     * @param CartasiCustomerPaymentsInterface $cartasiCustomerPayments
     * @param CartasiContractsService $cartasiContractService
     * @param EntityManager $entityManager
     * @param EmailService $emailService
     * @param EventManager $eventManager
     * @param TripPaymentTriesService $tripPaymentTriesService
     * @param string $url
     * @param CustomerDeactivationService $deactivationService
     */
    public function __construct(
        CartasiCustomerPaymentsInterface $cartasiCustomerPayments,
        CartasiContractsService $cartasiContractService,
        EntityManager $entityManager,
        EmailService $emailService,
        EventManager $eventManager,
        TripPaymentTriesService $tripPaymentTriesService,
        $url,
        CustomerDeactivationService $deactivationService
    ) {
        $this->cartasiCustomerPayments = $cartasiCustomerPayments;
        $this->cartasiContractService = $cartasiContractService;
        $this->entityManager = $entityManager;
        $this->emailService = $emailService;
        $this->eventManager = $eventManager;
        $this->tripPaymentTriesService = $tripPaymentTriesService;
        $this->url = $url;
        $this->deactivationService = $deactivationService;
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
            if ($customer->getPaymentAble()) {
                // enable hooks on the event that the customer doesn't have a valid contract
                $this->eventManager->trigger('notifyCustomerPay', $this, [
                    'customer' => $customer,
                    'tripPayment' => $tripPayment
                ]);
            }

            $this->disableCustomer($customer);
        }
    }

    /**
     * @var Customers $customer
     */
    private function disableCustomer(Customers $customer)
    {
        $customer->setPaymentAble(false);
        $customer->disable();

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
     * @param Webuser $webuser
     * @param boolean $avoidEmail
     * @param boolean $avoidCartasi
     * @param boolean $avoidPersistance
     * @param boolean $avoidDisableUser
     * @return CartasiResponse
     */
    public function tryTripPayment(
        TripPayments $tripPayment,
        Webuser $webuser,
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
            $webuser,
            $avoidDisableUser
        );
    }

    /**
     * tries to pay the trip amount
     * writes in database a record in the trip_payment_tries table
     *
     * @param Customers $customer
     * @param TripPayments $tripPayment
     * @param Webuser|null $webuser
     * @param boolean $avoidDisableUser
     * @return CartasiResponse
     */
    private function tryCustomerTripPayment(
        Customers $customer,
        TripPayments $tripPayment,
        Webuser $webuser = null,
        $avoidDisableUser = false
    ) {
        $response = $this->cartasiCustomerPayments->sendPaymentRequest(
            $customer,
            $tripPayment->getTotalCost(),
            $this->avoidCartasi
        );

        $this->entityManager->beginTransaction();

        try {
            $tripPaymentTry = $this->tripPaymentTriesService->generateTripPaymentTry(
                $tripPayment,
                $response->getOutcome(),
                $response->getTransaction(),
                $webuser
            );

            if ($response->getCompletedCorrectly()) {
                $this->markTripAsPayed($tripPayment);
            } else {
                $this->unpayableConsequences(
                    $customer,
                    $tripPayment,
                    $tripPaymentTry,
                    $avoidDisableUser
                );
            }

            $this->entityManager->persist($tripPaymentTry);
            $this->entityManager->flush();

            if (!$this->avoidPersistance) {
                $this->entityManager->commit();
            } else {
                $this->entityManager->rollback();
            }
        } catch (\Exception $e) {
            $this->entityManager->rollback();
            throw $e;
        }

        return $response;
    }

    /**
     * tries to pay a set of trips and
     * writes in database, for each trip, a record in the trip_payment_tries table
     *
     * @param Customers $customer
     * @param Trips[] $trips
     * @param Webuser $webuser
     * @param boolean $avoidDisableUser
     * @return CartasiResponse
     */
    public function tryTripPaymentGroup(
        Customers $customer,
        array $trips
    ) {
        $webuser = null;
        $avoidDisableUser = false;
        $this->avoidCartasi = false;
        $this->avoidPersistance = false;

        $response = null;
        $totalCost = 0;

        foreach ($trips as $trip) {
             $totalCost += $trip->getTripPayment()->getTotalCost();
        }

        if (!$this->cartasiContractService->hasCartasiContract($customer)) {
            return $response;
        }

        $response = $this->cartasiCustomerPayments->sendPaymentRequest(
            $customer,
            $totalCost,
            $this->avoidCartasi
        );

        $this->entityManager->beginTransaction();

        try {

            $tripPayment = $trip->getTripPayment();
            $tripPaymentTry = $this->tripPaymentTriesService->generateTripPaymentTry(
                $tripPayment,
                $response->getOutcome(),
                $response->getTransaction(),
                $webuser
            );

            if ($response->getCompletedCorrectly()) {
                $this->markTripAsPayed($tripPayment);
            } else {
//                    $this->unpayableConsequences(
//                        $customer,
//                        $tripPayment,
//                        $tripPaymentTry,
//                        $avoidDisableUser
//                    );


                $this->entityManager->persist($tripPaymentTry);
                $this->entityManager->flush();

            }

            if (!$this->avoidPersistance) {
                $this->entityManager->commit();
            } else {
                $this->entityManager->rollback();
            }
        } catch (\Exception $e) {
            $this->entityManager->rollback();
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
     * @param TripPaymentTries $tripPaymentTry
     * @param boolean $avoidDisableUser
     */
    private function unpayableConsequences(
        Customers $customer,
        TripPayments $tripPayment,
        TripPaymentTries $tripPaymentTry,
        $avoidDisableUser
    ) {
        // disable the customer
        if (!$avoidDisableUser) {
            $this->deactivationService->deactivateForTripPaymentTry(
                $customer,
                $tripPaymentTry
            );
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
