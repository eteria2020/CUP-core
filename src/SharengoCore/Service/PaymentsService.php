<?php

namespace SharengoCore\Service;

use Cartasi\Service\CartasiCustomerPayments;
use SharengoCore\Entity\Customers;
use SharengoCore\Entity\TripPayments;
use SharengoCore\Entity\TripPaymentTries;

use Doctrine\ORM\EntityManager;

class PaymentsService
{
    /**
     * @var CartasiCustomerPayments
     */
    private $cartasiCustomerPayments;

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var EmailService
     */
    private $emailService;

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

    public function __construct(
        CartasiCustomerPayments $cartasiCustomerPayments,
        EntityManager $entityManager,
        EmailService $emailService
    ) {
        $this->cartasiCustomerPayments = $cartasiCustomerPayments;
        $this->entityManager = $entityManager;
        $this->emailService = $emailService;
    }

    /**
     * tries to pay the trip amount
     * writes in database a record in the trip_payment_tries table
     *
     * @param Customers $customer
     * @param TripPayments $tripPayment
     * @param boolean $avoidEmail
     * @param boolean $avoidCartasi
     * @param boolean $avoidPersistance
     */
    public function tryTripPayment(
        Customers $customer,
        TripPayments $tripPayment,
        $avoidEmail = false,
        $avoidCartasi = false,
        $avoidPersistance = false
    ) {
        $this->avoidEmail = $avoidEmail;
        $this->avoidCartasi = $avoidCartasi;
        $this->avoidPersistance = $avoidPersistance;

        $response = $this->cartasiCustomerPayments->sendPaymentRequest(
            $customer,
            $tripPayment->getTotalCost(),
            $this->avoidCartasi
        );

        $this->entityManager->getConnection()->beginTransaction();

        try {
            if ($response->getCompletedCorrectly) {
                $this->markTripAsPayed($tripPayment);
            } else {
                $this->unpayableConsequences($customer, $tripPayment);
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
     */
    private function unpayableConsequences(Customers $customer, TripPayments $tripPayment)
    {
        // disable the customer
        $customer->disable();

        $this->entityManager->persist($customer);

        // set the trip payment as wrong payment
        $tripPayment->setWrongPayment();

        $this->entityManager->persist($tripPayment);
        $this->entityManager->flush();

        //notify the customer
        $this->notifyCustomerOfWrongPayment($customer, $tripPayment);
    }

    /**
     * @param Customers $customer
     * @param TripPayment $tripPayment
     */
    private function notifyCustomerOfWrongPayment(Customers $customer, TripPayments $tripPayment)
    {
        $content = sprintf(
            file_get_contents(__DIR__.'/../../../view/emails/wrong-payment-it_IT.html'),
            $customer->getName(),
            $customer->getSurname()
        );

        $attachments = [
            'bannerphono.jpg' => __DIR__.'/../../../../../public/images/bannerphono.jpg'
        ];

        if (!$this->avoidEmail) {
            $this->emailService->sendEmail(
                $customer->getEmail(),
                'SHARENGO - ERRORE NEL PAGAMENTO',
                $content,
                $attachments
            );
        }
    }
}
