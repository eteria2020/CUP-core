<?php

namespace SharengoCore\Service;

use SharengoCore\Entity\Trips;
use SharengoCore\Entity\TripPayments;
use SharengoCore\Entity\Customers;
use Cartasi\Service\CartasiContractsService;

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
     * @var array
     */
    private $websiteConfig;

    public function __construct(
        FaresService $faresService,
        TripFaresService $tripFaresService,
        EntityManager $entityManager,
        Client $httpClient,
        Url $url,
        CartasiContractsService $cartasiContractsService,
        array $websiteConfig
    ) {
        $this->faresService = $faresService;
        $this->tripFaresService = $tripFaresService;
        $this->entityManager = $entityManager;
        $this->httpClient = $httpClient;
        $this->url = $url;
        $this->cartasiContractsService = $cartasiContractsService;
        $this->websiteConfig = $websiteConfig;
    }

    /**
     * process a trip to compute its cost and writes it to database in the
     * trip_payments and the trip_payments_tries tables
     *
     * @param Trips $trip
     */
    public function computeTripCost(Trips $trip)
    {
        $tripPayment = $this->retrieveTripCost($trip);

        $this->entityManager->getConnection()->beginTransaction();

        try {
            $this->saveTripPayment($tripPayment);

            if ($trip->canBePayed()) {
                $this->tryTripPayment($trip->getCustomer(), $tripPayment);
            } else {
                $this->notifyUserHeHasToPay($trip->getCustomer());
            }

            $this->entityManager->getConnection()->commit();
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
     * @param TripBills[] $tripBills
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
     * @param TripPayment $tripPayment
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
        $this->sendPaymentRequest($customer, $tripPayment->getTotalCost());
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
        $contractNumber = $this->cartasiContractsService->getCartasiContractNumber($customer);

        if (!$contractNumber) {
            return false;
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

        $response = $this->httpClient->send($request);var_dump($response); die;

        return true;
    }

    /**
     * notifies the user requesting him to do the first payment
     *
     * @param Customers $customer
     */
    private function notifyUserHeHasToPay(Customers $customer)
    {

    }
}
