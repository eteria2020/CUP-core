<?php

namespace SharengoCore\Service\Partner;

use Doctrine\ORM\EntityManager;
use SharengoCore\Service\TripsService;
use SharengoCore\Service\ExtraPaymentsService;

use SharengoCore\Entity\TripPayments;
use SharengoCore\Entity\Customers;
use SharengoCore\Entity\Repository\PartnersRepository;

use Cartasi\Entity\CartasiResponse;
use Cartasi\Entity\Transactions;
use Cartasi\Service\CartasiContractsService;


use Zend\EventManager\EventManager;
use Zend\Http\Request;
use Zend\Http\Client;

class TelepassPayService {

    //const PAYMENT_LABEL = 'TELEPASSPAY';
    const PAYMENT_SUCCESSFUL = 'OK';
    const PAYMENT_FAIL = 'KO';

    private $code ='telepass';
    private $currency ='EUR';

    /**
     *
     * @var Partners partner
     */
    private $partner;

    /**
     *
     * @var array params
     */
    private $parms;


    /**
     *
     * @var EntityManager entityManager
     */
    private $entityManager;

    /**
     *
     * @var EventManager eventManager
     */
    private $eventManager;

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

    /**
     *
     * @var HttpClient httpClient
     */
    private $httpClient;

    /**
     *
     * @var CartasiContractsService cartasiContractsService
     */
    private $cartasiContractsService;

    /**
     *
     * @var PartnersRepository partnersRepository
     */
    private $partnersRepository;

    /**
     * TelepassPayService constructor.
     * @param EntityManager $entityManager
     * @param EventManager $eventManager
     * @param TripsService $tripsService
     * @param ExtraPaymentsService $extraPaymentsService
     * @param CartasiContractsService $cartasiContractsService
     * @param PartnersRepository $partnersRepository
     */
    public function __construct(
        EntityManager $entityManager,
        EventManager $eventManager,
        TripsService $tripsService,
        ExtraPaymentsService $extraPaymentsService,
        CartasiContractsService $cartasiContractsService,
        PartnersRepository $partnersRepository
    ) {;
        $this->entityManager = $entityManager;
        $this->eventManager = $eventManager;
        $this->tripsService = $tripsService;
        $this->extraPaymentsService = $extraPaymentsService;
        $this->cartasiContractsService = $cartasiContractsService;
        $this->partnersRepository = $partnersRepository;

        $this->httpClient = new Client();
        $this->httpClient->setMethod(Request::METHOD_POST);
        $this->httpClient->setOptions([
            'maxredirects' => 0,
            'timeout' => 90
        ]);

        $this->partner = $this->partnersRepository->findOneBy(array('code' => $this->code, 'enabled' => true));
        if($this->partner instanceof Partners) {
            $this->parms = $this->partner->getParamsDecode();

            $this->httpClient->setHeaders(
                array(
                    'Content-type' => 'application/json',
                    'charset' => 'UTF-8',
                    'Authorization' => $this->parms['payments']['authorization']
                )
            );
        }
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
    private function sendPreAthorization(
        $userId,
        $referenceId,
        array $metadata,
        $amount,
        $currency,
        &$response) {

        $uriPreAuth ='/pay/preauth';
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
            $request->setUri($this->parms['payments']['uri'] . $uriPreAuth);
//            $request->setMetadata('POST');
//            $request->getPost()->set('userId', $userId);
//            $request->getPost()->set('referenceId', $referenceId);
//            $request->getPost()->set('metadata', $metadata);
//            $request->getPost()->set('amount', $amount);
//            $request->getPost()->set('currency', $currency);
//            $request->getHeaders()->addHeaders(
//                array(
//                    //'Content-type' => 'application/json'
//                    'Authorization' => $this->parms['payments']['authorization']
//                )
//            );


            $this->httpClient->setUri($this->parms['payments']['uri'] . $uriPreAuth);
            $this->httpClient->setRawBody($json);
            $this->httpClient->setHeaders(
                array(
                    'Content-type' => 'application/json',
                    'charset' => 'UTF-8',
                    'Authorization' => $this->parms['payments']['authorization']
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

            if (isset($response['preAuthId'])) {    // if there is an error (mis match on referenceId) this field dosn't exists
                if (strlen(trim($response['preAuthId']))>0) {
                    $result = true;
                }
            }
        } catch (\Exception $ex) {
            $response = null;
        }

        return $result;
    }

    /**
     * Charging an account
     * After a ride is complete, its cost can be charged to the associated user’s account by calling the charge endpoint.
     *
     * @param string $referenceId The unique reference ID on partners' side (for Share’ngo, this should be the ID of the reservation for which a vehicle is about to be unlocked)
     * @param string $email
     * @param string $type Type of payment object (trip, subscription, package, extra)
     * @param int $fleetId Fleet index
     * @param int  $amount The amount of money (in Euro cents) to be charged on the payment gateway
     * @param string $currency The currency of the pre-authorization (ISO 4217 Currency Codes)
     * @param string $curlResponse Response of server
     * @return boolean
     */
    private function tryCharginAccount(
        $referenceId,
        $email,
        $type,
        $fleetId,
        $amount,
        $currency,
        &$response) {

        $result = false;
        $response = null;

        try {

            $json = json_encode(
                array(
                    'referenceId' => strval($referenceId),
                    'email' => $email,
                    'type' => strtoupper($type),
                    'fleetId' => $fleetId,
                    'amount' => $amount,
                    'currency' => $currency
                )
            );

            $this->httpClient->setUri($this->params['payments']['uri']);
            $this->httpClient->setMethod(Request::METHOD_POST);
            $adapter = new \Zend\Http\Client\Adapter\Curl();
            $this->httpClient->setAdapter($adapter);

            $adapter->setOptions(array(
                'curloptions' => array(
                    CURLOPT_SSLVERSION => 6, //tls1.2
                    //CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_VERBOSE => 0,
                    CURLOPT_SSL_VERIFYHOST => 0,
                    CURLOPT_SSL_VERIFYPEER => 0
                )
            ));

            $this->httpClient->setRawBody($json);
            $httpResponse  = $this->httpClient->send();

            if($httpResponse->isSuccess()) {
                $response = $httpResponse->getBody();
                json_decode($response, true);    // check json format
                $result = (json_last_error() == JSON_ERROR_NONE);
            } else {
                $response = "HTTP error:".$httpResponse->getStatusCode();
            }

        } catch (\Exception $ex) {
            var_dump("tryCharginAccount();ERR;EXC;".$ex->getLine().";".$ex->getMessage());
            $response = $ex->getMessage();
        }

        return $result;
    }


//    /**
//     * Send a payment request to Telepass in two step (pre-authorization and carging account).
//     *
//     * @param Customers $customer
//     * @param integer $amount
//     * @param boolean $avoidHittingTelepassPay
//     * @return CartasiResponse
//     */
//    public function sendPaymentRequest(
//        Customers $customer,
//        $amount,
//        $avoidHittingTelepassPay = false
//    ) {
//        $response = null;
//
//        if(!$avoidHittingTelepassPay) {
//            $response = new CartasiResponse(false, 'KO', null);
//
//            $responseTelepass = null;
//            $transaction = $this->newTransactionCustomer($customer, $amount);
//
//            if($this->cartasiContractsService->hasCartasiContract($customer)) {
//                $contract = $this->cartasiContractsService->getCartasiContract($customer);
//
//                $transaction->setContract($contract);
//                $response = new CartasiResponse(false, 'KO', $transaction);
//
//                if($this->sendPreAthorization(
//                    $customer->getEmail(),
//                    $transaction->getId(),
//                    array(
//                        'reason'=> 'trip payment',
//                        'transaction' => $transaction->getId()),
//                    $amount,
//                    $this->currency,
//                    $responseTelepass)) {
//
//                    $transaction->setCodAut($responseTelepass['preAuthId']);
//
//                    if($this->tryCharginAccount(
//                        $transaction->getId(),
//                        $responseTelepass['preAuthId'],
//                        $amount,
//                        $this->currency,
//                        $responseTelepass)) {
//
//                        $transaction->setOutcome('OK');
//                    }
//                }
//            }
//
//            if(is_null($responseTelepass)) { // if it's happen a system error like remote server down
//                return null;
//            }
//
//            $transaction->setMessage(substr(json_encode($responseTelepass), 0, 255));
//            $transaction->setDatetime(date_create());
//            $this->entityManager->merge($transaction);
//            $this->entityManager->flush();
//
//            if($transaction->getOutcome()=='OK') {
//                $response = new CartasiResponse(true, 'OK', $transaction);
//            }
//        }
//
//        return $response;
//
//    }


    /**
     * Send a payment request to Nugo in two step (pre-authorization and carging account).
     *
     * @param Customers $customer
     * @param integer $amount
     * @param boolean $avoidHittingPay
     * @return CartasiResponse
     */
    public function sendTripPaymentRequest(
        TripPayments $tripPayment,
        $avoidHittingPay = false
    ) {
        $customer = $tripPayment->getCustomer();
        $response = null;

        if(!$avoidHittingPay) {
            $response = new CartasiResponse(false, self::PAYMENT_FAIL, null);

            $responsePayment = null;
            $amount = $tripPayment->getTotalCost();
            $transaction = $this->newTransactionCustomer($customer, $amount);

            if($this->cartasiContractsService->hasCartasiContract($customer)) {
                $contract = $this->cartasiContractsService->getCartasiContract($customer);

                $transaction->setContract($contract);
                $response = new CartasiResponse(false, self::PAYMENT_FAIL, $transaction);

                if($this->tryCharginAccount(
                    $tripPayment->getTripId(),
                    $customer->getEmail(),
                    'TRIP',
                    $tripPayment->getTrip()->getFleet()->getId(),
                    $amount,
                    $this->currency,
                    $responsePayment)) {

                    $jsonResponse = json_decode($responsePayment, true);
                    if ($jsonResponse['chargeSuccessful']===true) {
                        $transaction->setOutcome(self::PAYMENT_SUCCESSFUL);
                    }
                }

                $transaction->setMessage($responsePayment);

//                $this->eventManager->trigger('notifyPartnerCustomerStatus', $this, [
//                    'customer' => $customer
//                ]);
            }

            $this->entityManager->merge($transaction);
            $this->entityManager->flush();

            if($transaction->getOutcome()==self::PAYMENT_SUCCESSFUL) {
                $response = new CartasiResponse(true, self::PAYMENT_SUCCESSFUL, $transaction);
            }
        }

        return $response;

    }



    /**
     * Create a new Tpay transaction from customers and amount
     * 
     * @param Customers $customer
     * @param integer $amount
     * @return Transactions
     */
    private function newTransactionCustomer (Customers $customer, $amount) {

        $transaction = new Transactions();
        $transaction->setDatetime(date_create());
        $transaction->setName($customer->getName());
        $transaction->setSurname($customer->getSurname());
        $transaction->setEmail($customer->getEmail());
        $transaction->setAmount($amount);
        $transaction->setCurrency($this->currency);
        $transaction->setOutcome('KO');

        $transaction->setBrand('TPAY');
        $transaction->setRegion('EUROPE');
        $transaction->setCountry('ITA');
        $transaction->setMessage('KO - init fail');
        $transaction->setProductType('TELEPASS+TPAY+PREPAID+-+-N');
        $transaction->setIsFirstPayment(false);

        $this->entityManager->persist($transaction);
        $this->entityManager->flush();

        return $transaction;
    }
}
