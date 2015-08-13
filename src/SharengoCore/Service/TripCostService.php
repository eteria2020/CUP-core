<?php

namespace SharengoCore\Service;

use SharengoCore\Entity\Trips;
use SharengoCore\Entity\TripPayments;
use SharengoCore\Entity\TripPaymentTries;
use SharengoCore\Entity\Customers;
use Cartasi\Service\CartasiContractsService;
use Cartasi\Entity\Repository\TransactionsRepository;
use Cartasi\Entity\Transactions;
use SharengoCore\Service\SimpleLoggerService as Logger;

use Doctrine\ORM\PersistentCollection;
use Doctrine\ORM\EntityManager;
use Zend\Http\Client;
use Zend\Http\Request;
use Zend\View\Helper\Url;
use Zend\Uri\Http as HttpUri;

class TripCostService
{
    /**
     * @var FaresService
     */
    private $faresService;

    /**
     * @var TripFaresService
     */
    private $tripFaresService;

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var Client
     */
    private $httpClient;

    /**
     * @var Url
     */
    private $url;

    /**
     * @var CartasiContractsService
     */
    private $cartasiContractsService;

    /**
     * @var TransactionsRepository
     */
    private $transactionsRepository;

    /**
     * @var array
     */
    private $websiteConfig;

    /**
     * @var EmailService
     */
    private $emailService;

    /**
     * @var boolean
     */
    private $avoidEmail = true;

    /**
     * @var boolean
     */
    private $avoidCartasi = true;

    /**
     * @var Logger
     */
    private $logger;

    public function __construct(
        FaresService $faresService,
        TripFaresService $tripFaresService,
        EntityManager $entityManager,
        Client $httpClient,
        Url $url,
        CartasiContractsService $cartasiContractsService,
        TransactionsRepository $transactionsRepository,
        array $websiteConfig,
        EmailService $emailService,
        Logger $logger
    ) {
        $this->faresService = $faresService;
        $this->tripFaresService = $tripFaresService;
        $this->entityManager = $entityManager;
        $this->httpClient = $httpClient;
        $this->url = $url;
        $this->cartasiContractsService = $cartasiContractsService;
        $this->transactionsRepository = $transactionsRepository;
        $this->websiteConfig = $websiteConfig;
        $this->emailService = $emailService;
        $this->logger = $logger;
    }

    /**
     * process a trip to compute its cost and writes it to database in the
     * trip_payments and the trip_payments_tries tables
     * the three boolean parameters allow the run the function without side effects
     *
     * @param Trips $trip
     * @param boolean $avoidPersistance
     * @param boolean $avoidEmail
     * @param boolean $avoidCartasi
     */
    public function computeTripCost(
        Trips $trip,
        $avoidCartasi = true,
        $avoidPersistance = true,
        $avoidEmail = true
    ) {
        $this->avoidEmail = $avoidEmail;
        $this->avoidCartasi = $avoidCartasi;

        $tripPayment = $this->retrieveTripCost($trip);

        $this->entityManager->getConnection()->beginTransaction();

        try {
            $this->saveTripPayment($tripPayment);
            if ($trip->canBePayed()) {
                $this->tryTripPayment($trip->getCustomer(), $tripPayment);
            } else {
                $this->notifyCustomerHeHasToPay($trip->getCustomer());
            }

            if (!$avoidPersistance) {
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
     * @param Trips $trip
     * @return TripPayments
     */
    private function retrieveTripCost(Trips $trip)
    {
        // retrieve the fare for the trip
        $fare = $this->faresService->getFare();

        // compute the payable minutes of the trip
        $tripMinutes = $this->cumulateMinutes($trip->getTripBills());

        // compute the minutes of parking
        $parkMinutes = $this->computeParkMinutes($trip, $tripMinutes);

        // retrieve the discount applied to the trip
        $discountPercentage = $trip->getDiscountPercentage();

        // compute the trip cost
        $cost = $this->tripFaresService->userTripCost($fare, $tripMinutes, $parkMinutes, $discountPercentage);

        return new TripPayments($trip, $fare, $tripMinutes, $parkMinutes, $discountPercentage, $cost);
    }

    /**
     * computes the total number of payable minutes of a trip, summing the
     * length of all the trip bills intervals
     *
     * @param PersistentCollection[TripBills] $tripBills
     * @return int
     */
    private function cumulateMinutes(PersistentCollection $tripBills)
    {
        $minutes = 0;

        foreach ($tripBills as $tripBill) {
            $minutes += $tripBill->getMinutes();
        }

        return $minutes;
    }

    /**
     * computes the minutes of parking of a trip
     *
     * @param Trips $trip
     * @param int $tripMintues
     * @return int
     */
    private function computeParkMinutes(Trips $trip, $tripMinutes)
    {
        // 29sec -> 0min, 30sec -> 1 min
        $tripParkMinutes = ceil(($trip->getParkSeconds() - 29) / 60);

        // we don't want to have more parking minutes than the payable length
        // of a trip
        return min($tripMinutes, $tripParkMinutes);
    }

    /**
     * persists the newly created tripPayment record
     *
     * @param TripPayments $tripPayment
     */
    private function saveTripPayment(TripPayments $tripPayment)
    {
        $this->entityManager->persist($tripPayment);
        $this->entityManager->flush();
    }

    /**
     * tries to pay the trip amount
     * writes in database a record in the trip_payment_tries table
     *
     * @param Trips $trip
     */
    private function tryTripPayment(Customers $customer, TripPayments $tripPayment)
    {
        $response = $this->sendPaymentRequest($customer, $tripPayment->getTotalCost());

        if ($response->completedCorrectly) {
            $this->markTripAsPayed($tripPayment);
        } else {
            $this->unpayableConsequences($customer, $tripPayment);
        }

        $tripPaymentTry = new TripPaymentTries($tripPayment, $response->outcome, $response->transaction);

        $this->entityManager->persist($tripPaymentTry);
        $this->entityManager->flush();
    }

    /**
     * sends a request for the payment of the amount for the given user
     * returns a boolean indicating if the operation was successful
     *
     * @param Customers $customer
     * @param int $amount
     * @return boolean
     */
    private function sendPaymentRequest(Customers $customer, $amount)
    {
        $ret = new \StdClass;
        $ret->completedCorrectly = false;
        $ret->outcome = 'KO';
        $ret->transaction = null;

        $contractNumber = $this->cartasiContractsService->getCartasiContractNumber($customer);

        if (!$contractNumber) {
            return $ret;
        }

        $uri = new HttpUri($this->websiteConfig['uri']);
        $url = $this->url->__invoke(
            'cartasi/pagamento-ricorrente',
            [],
            [
                'force_canonical' => true,
                'query' => [
                    'contract' => $contractNumber,
                    'amount' => $amount
                ],
                'uri' => $uri
            ]
        );

        $request = new Request();
        $request->setUri($url);

        if (!$this->avoidCartasi) {
            $response = $this->httpClient->send($request);

            if ($response->getstatusCode() === 200) {
                $parsedBody = json_decode($response->getBody());

                $ret->outcome = $parsedBody->outcome;
                $ret->completedCorrectly = ($ret->outcome === 'OK');
                $ret->transaction = $this->transactionsRepository->findOneById($parsedBody->codTrans);
            }
        }

        return $ret;
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
     * @param Transactions $transaction
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
     * @param Transactions $transaction
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

    /**
     * notifies the user requesting him to do the first payment
     *
     * @param Customers $customer
     */
    private function notifyCustomerHeHasToPay(Customers $customer)
    {
        $link = ''; //TODO: retrieve correct link for first payment

        $content = sprintf(
            file_get_contents(__DIR__.'/../../../view/emails/first-payment-request-it_IT.html'),
            $customer->getName(),
            $customer->getSurname(),
            $link
        );

        $attachments = [
            'bannerphono.jpg' => __DIR__.'/../../../../../public/images/bannerphono.jpg'
        ];

        if (!$this->avoidEmail) {
            $this->emailService->sendEmail(
                $customer->getEmail(),
                'SHARENGO - RICHIESTA DI PRIMO PAGAMENTO',
                $content,
                $attachments
            );
        }
    }
}
