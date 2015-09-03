<?php

namespace SharengoCore\Service;

use Cartasi\Service\CartasiCustomerPayments;
use Cartasi\Service\CartasiContractsService;
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
        $url
    ) {
        $this->cartasiCustomerPayments = $cartasiCustomerPayments;
        $this->cartasiContractService = $cartasiContractService;
        $this->entityManager = $entityManager;
        $this->emailService = $emailService;
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

        if ($customer->getPaymentAble()) {
            if ($this->cartasiContractService->hasCartasiContract($customer)) {
                $this->tryCustomerTripPayment(
                    $customer,
                    $tripPayment
                );
            } else {
                $this->disableCustomerForPayment($customer);
                $this->notifyCustomerHeHasToPay($customer);
            }
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
     * notifies the user requesting him to do the first payment
     *
     * @param Customers $customer
     */
    private function notifyCustomerHeHasToPay(Customers $customer)
    {
        $date = date_create('midnight +7 days');
        $content = sprintf(
            file_get_contents(__DIR__.'/../../../view/emails/first-payment-request-it_IT.html'),
            $customer->getName(),
            $customer->getSurname(),
            $date->format('d/m/Y')
        );

        $attachments = [
            'bannerphono.jpg' => $this->url . '/assets-modules/sharengo-core/images/bannerphono.jpg',
            'barbarabacci.jpg' => $this->url . '/assets-modules/sharengo-core/images/barbarabacci.jpg'
        ];

        if (!$this->avoidEmail) {
            $this->emailService->sendEmail(
                $customer->getEmail(),
                'Pagamento delle tue corse a debito',
                $content,
                $attachments
            );
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
     * @return CartasiResponse
     */
    public function tryTripPayment(
        TripPayments $tripPayment,
        $avoidEmail = false,
        $avoidCartasi = false,
        $avoidPersistance = false
    ) {
        $this->avoidEmail = $avoidEmail;
        $this->avoidCartasi = $avoidCartasi;
        $this->avoidPersistance = $avoidPersistance;

        $customer = $tripPayment->getCustomer();

        return $this->tryCustomerTripPayment(
            $customer,
            $tripPayment
        );
    }

    /**
     * tries to pay the trip amount
     * writes in database a record in the trip_payment_tries table
     *
     * @param Customers $customer
     * @param TripPayments $tripPayment
     * @return CartasiResponse
     */
    private function tryCustomerTripPayment(
        Customers $customer,
        TripPayments $tripPayment
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
     */
    private function unpayableConsequences(Customers $customer, TripPayments $tripPayment)
    {
        // disable the customer
        $customer->disable();
        $customer->setPaymentAble(false);

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
            'bannerphono.jpg' => $this->url . '/assets-modules/sharengo-core/images/bannerphono.jpg',
            'barbarabacci.jpg' => $this->url . '/assets-modules/sharengo-core/images/barbarabacci.jpg'
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
