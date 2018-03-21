<?php

namespace SharengoCore\Service;

use Doctrine\ORM\EntityManager;
use SharengoCore\Service\TripsService;
use SharengoCore\Service\ExtraPaymentsService;
use SharengoCore\Entity\TripPayments;
use Cartasi\Entity\CartasiResponse;
use Cartasi\Service\CartasiContractsService;
use Cartasi\Entity\Transactions;
use Cartasi\Entity\Contracts;

use Zend\Http\Request;
use Zend\Http\Client;

class TelepassPayService {

    /**
     *
     * @var array telepassPayConfig
     */
    private $telepassPayConfig;

    /**
     *
     * @var EntityManager entityManager
     */
    private $entityManager;

    /**
     *
     * @var TripsService tripsService
     */
    private $tripsService;

    /**
     *
     * @var ExtraPaymentsService extraPaymentsService
     */
    private $extraPaymentsService;

    private $httpClient;

    private $cartasiContractsService;

    public function __construct(
        array $telepassPayConfig,
        EntityManager $entityManager,
        TripsService $tripsService,
        ExtraPaymentsService $extraPaymentsService,
        CartasiContractsService $cartasiContractsService
    ) {
        $this->telepassPayConfig = $telepassPayConfig;
        $this->entityManager = $entityManager;
        $this->tripsService = $tripsService;
        $this->extraPaymentsService = $extraPaymentsService;
        $this->cartasiContractsService = $cartasiContractsService;

        $this->httpClient = new Client();
        $this->httpClient->setMethod(Request::METHOD_POST);
        $this->httpClient->setOptions([
             'maxredirects' => 0,
            'timeout' => 90
        ]);

        $this->httpClient->setHeaders(
            array(
                'Content-type' => 'application/json',
                'charset' => 'UTF-8',
                'Authorization' => $this->telepassPayConfig['authorization']
            )
        );
    }

    /**
     * Pre-authorizing a payment
     * Payments for a registered user can be pre-authorized by calling the preauth endpoint.
     *
     * @param string $userId The ID of the user on partners' side to link the payment to (for Share’ngo, this should be the ID of the user that’s about to use the vehicle)
     * @param string $referenceId The unique reference ID on partners' side (for Share’ngo, this should be the ID of the reservation for which a vehicle is about to be unlocked)
     * @param array $metadata Optional, free-form JSON structure to hold additional metadata for the payment (e.g., a reason for the payment)
     * @param int $amount The amount of money (in Euro cents) to be preauthorized on the payment gateway
     * @param string $currency The currency of the pre-authorization (ISO 4217 Currency Codes)
     * @param array $response Response from Telepass
     */
    public function sendPreAthorization(
        $userId,
        $referenceId,
        array $metadata,
        $amount,
        $currency,
        &$response) {

        $result = false;
        $response = null;

        try {
//            $uri = new Http($this->telepassConfig['uri']);
//            $url = $this->url->__invoke(
//                    'pay/preauth', [], [
//                'force_canonical' => true,
//                'query' => [
//                    'userId' => $userId,
//                    'referenceId' => $referenceId,
//                    'metadata' => json_encode($metadata),
//                    'amount' => $amount,
//                    'currency' => $currency
//                ],
//                'uri' => $uri
//                    ]
//            );

            $json = json_encode(
                array(
                    'userId' => $userId,
                    'referenceId' => $referenceId,
                    'metadata' => json_encode($metadata),
                    'amount' => $amount,
                    'currency' => $currency
                )
            );

            $request = new Request();
            $request->setUri($this->telepassPayConfig['uri']. '/pay/preauth');
//            $request->setMetadata('POST');
//            $request->getPost()->set('userId', $userId);
//            $request->getPost()->set('referenceId', $referenceId);
//            $request->getPost()->set('metadata', $metadata);
//            $request->getPost()->set('amount', $amount);
//            $request->getPost()->set('currency', $currency);
//            $request->getHeaders()->addHeaders(
//                array(
//                    //'Content-type' => 'application/json'
//                    'Authorization' => $this->telepassPayConfig['authorization']
//                )
//            );


            $this->httpClient->setUri($this->telepassPayConfig['uri']. '/pay/preauth');
            $this->httpClient->setRawBody($json);
            $this->httpClient->setHeaders(
                array(
                    'Content-type' => 'application/json',
                    'charset' => 'UTF-8',
                    'Authorization' => $this->telepassPayConfig['authorization']
                )
            );

//            $this->httpClient->setParameterPost(
//                array(
//                    'userId' => $userId,
//                    'referenceId' => $referenceId,
//                    'metadata' => json_encode($metadata),
//                    'amount' => $amount,
//                    'currency' => $currency
//                )
//            );

            //$httpResponse = $this->httpClient->send($request);
            $httpResponse = $this->httpClient->send();

            $response = json_decode($httpResponse->getBody(), true);
            //var_dump($response);

            if (isset($response['preAuthId'])) {
                if (strlen(trim($response['preAuthId']))>0) {
                    $result = true;
                }
            }

        } catch (\Exception $ex) {
            $response = array(
                'uri' => '/pay/preauth',
                'status' => 401,
                'statusFromProvider' => false,
                'message' => $ex->getMessage());
        }

        return $result;
    }

    /**
     * Charging an account
     * After a ride is complete, its cost can be charged to the associated user’s account by calling the charge endpoint.
     *
     * @param string $referenceId The unique reference ID on partners' side (for Share’ngo, this should be the ID of the reservation for which a vehicle is about to be unlocked)
     * @param string  $preAuthId The unique ID associated with the preauthorization, obtained via a /pay/preauth request
     * @param int  $amount The amount of money (in Euro cents) to be charged on the payment gateway
     * @param string $currency The currency of the pre-authorization (ISO 4217 Currency Codes)
     * @param array $response Response of server
     * @return boolean
     */
    public function tryCharginAccount(
        $referenceId,
        $preAuthId,
        $amount,
        $currency,
        &$response) {

        $result = false;
        $response = null;

        try {
            $json = json_encode(
                array(
                    'referenceId' => $referenceId,
                    'preAuthId' => $preAuthId,
                    'amount' => $amount,
                    'currency' => $currency
                )
            );

            $request = new Request();
            $request->setUri($this->telepassPayConfig['uri']. '/pay/charge');

            $this->httpClient->setUri($this->telepassPayConfig['uri']. '/pay/charge');
            $this->httpClient->setRawBody($json);

            $httpResponse = $this->httpClient->send();
            $response = json_decode($httpResponse->getBody(), true);

            if (isset($response['chargeSuccessful'])) {
                if (strtolower(trim($response['chargeSuccessful']))==='true') {
                    $result = true;
                }
            }
        } catch (\Exception $ex) {
            $response = array(
                'uri' => '/pay/charge',
                'status' => 401,
                'statusFromProvider' => false,
                'message' => $ex->getMessage());
        }

        return $result;
    }

    /**
     * Send a payment request to TelepassPay (via Urbi).
     *
     * @param TripPayments $tripPayment
     * @param boolean $avoidHittingTelepassPay
     * @return array
     */
    public function sendPaymentRequest(
        TripPayments $tripPayment,
        $avoidHittingTelepassPay = false
    ) {

        $response = new CartasiResponse(false, 'KO', null);

        if(!$avoidHittingTelepassPay) {
            $responseTelepass = '';
            $currency = 'EUR';
            $customer  = $tripPayment->getCustomer();

            if($this->cartasiContractsService->hasCartasiContract($customer)) {
                $contract = $this->cartasiContractsService->getCartasiContract($customer);
                $transaction = $this->newTransaction($contract,
                    $tripPayment->getTotalCost(),
                    $currency);

                $response = new CartasiResponse(false, 'KO', $transaction);

                if($this->sendPreAthorization(
                    $customer->getId(),
                    $tripPayment->getId(),
                    array(
                        'reason'=> 'trip payment',
                        'transaction' => $transaction->getId()),
                    $tripPayment->getTotalCost(),
                    $currency,
                    $responseTelepass)) {

                    $transaction->setCodAut($response['preAuthId']);

                    if($this->tryCharginAccount(
                        $tripPayment->getId(),
                        $response['preAuthId'],
                        $tripPayment->getTotalCost(),
                        $currency,
                        $responseTelepass)) {

                        $transaction->setOutcome('OK');
                    }
                }
            }

            $transaction->setMessage(json_encode($responseTelepass));
            $transaction->setDatetime(date_create());
            $this->entityManager->persist($transaction);
            $this->entityManager->flush();

            if($transaction->getOutcome()=='OK') {
                $response = new CartasiResponse(true, 'OK', $transaction);
            }
        }

        return $response;
    }

    private function newTransaction(Contracts $contract, $amount, $currency) {

        $transaction = new Transactions();
        $transaction->setContract($contract);
        $transaction->setEmail($contract->getCustomer()->getEmail());
        $transaction->setAmount($amount);
        $transaction->setCurrency($currency);
        $transaction->setName($contract->getCustomer()->getName());
        $transaction->setSurname($contract->getCustomer()->getSurname());

        $transaction->setBrand('TPAY');
        $transaction->setOutcome('KO');
        $transaction->setDatetime(date_create());
        $transaction->setRegion('EUROPE');
        $transaction->setCountry('ITA');
        $transaction->setProductType('TELEPASS+TPAY+PREPAID+-+-N');
        $transaction->setIsFirstPayment(false);

        $this->entityManager->persist($transaction);
        $this->entityManager->flush();

        return $transaction;
    }
}
