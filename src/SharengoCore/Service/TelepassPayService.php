<?php

namespace SharengoCore\Service;

use Doctrine\ORM\EntityManager;
use SharengoCore\Service\TripsService;
use SharengoCore\Service\ExtraPaymentsService;
use SharengoCore\Entity\TripPayments;
use Cartasi\Entity\CartasiResponse;
use Cartasi\Service\CartasiContractsService;
use Cartasi\Entity\Transactions;
use SharengoCore\Entity\Repository\PartnersRepository;

use Zend\Http\Request;
use Zend\Http\Client;

class TelepassPayService {

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

    public function __construct(
        EntityManager $entityManager,
        TripsService $tripsService,
        ExtraPaymentsService $extraPaymentsService,
        CartasiContractsService $cartasiContractsService,
        PartnersRepository $partnersRepository
    ) {;
        $this->entityManager = $entityManager;
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
        $this->parms = $this->partner->getParamsDecode();

        $this->httpClient->setHeaders(
            array(
                'Content-type' => 'application/json',
                'charset' => 'UTF-8',
                'Authorization' => $this->parms['payments']['authorization']
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
            //var_dump($response);

            if (isset($response['preAuthId'])) {
                if (strlen(trim($response['preAuthId']))>0) {
                    $result = true;
                }
            }

        } catch (\Exception $ex) {
            $response = array(
                'uri' => $uriPreAuth,
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
    private function tryCharginAccount(
        $referenceId,
        $preAuthId,
        $amount,
        $currency,
        &$response) {

        $uriCharge = '/pay/charge';
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
            $request->setUri($this->parms['payments']['uri'] . $uriCharge);

            $this->httpClient->setUri($this->parms['payments']['uri'] . $uriCharge);
            $this->httpClient->setRawBody($json);

            $httpResponse = $this->httpClient->send();
            $response = json_decode($httpResponse->getBody(), true);

            if (isset($response['chargeSuccessful'])) {
                if ($response['chargeSuccessful']==true) {
                    $result = true;
                }
            }
        } catch (\Exception $ex) {
            $response = array(
                'uri' => $uriCharge,
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
     * @return CartasiResponse
     */
    public function sendPaymentRequest(
        TripPayments $tripPayment,
        $avoidHittingTelepassPay = false
    ) {

        $response = new CartasiResponse(false, 'KO', null);

        if(!$avoidHittingTelepassPay) {
            $responseTelepass = '';
            $transaction = $this->newTransaction($tripPayment);
            $customer  = $tripPayment->getCustomer();

            if($this->cartasiContractsService->hasCartasiContract($customer)) {
                $contract = $this->cartasiContractsService->getCartasiContract($customer);

                $transaction->setContract($contract);
                //var_dump($transaction->getId());
                $response = new CartasiResponse(false, 'KO', $transaction);

                if($this->sendPreAthorization(
                    $customer->getEmail(),
                    $transaction->getId(),
                    array(
                        'reason'=> 'trip payment',
                        'transaction' => $transaction->getId()),
                    $tripPayment->getTotalCost(),
                    $this->currency,
                    $responseTelepass)) {

                    var_dump($responseTelepass);
                    $transaction->setCodAut($responseTelepass['preAuthId']);

                    if($this->tryCharginAccount(
                        $transaction->getId(),
                        $responseTelepass['preAuthId'],
                        $tripPayment->getTotalCost(),
                        $this->currency,
                        $responseTelepass)) {

                        $transaction->setOutcome('OK');
                    }
                }
            }

            $transaction->setMessage(json_encode($responseTelepass));
            $transaction->setDatetime(date_create());
            $this->entityManager->merge($transaction);
            $this->entityManager->flush();

            if($transaction->getOutcome()=='OK') {
                $response = new CartasiResponse(true, 'OK', $transaction);
            }
        }

        return $response;
    }

    /**
     * Create a new Tpay transaction.
     *
     * @param TripPayments $tripPayment
     * @return Transactions
     */
    private function newTransaction (TripPayments $tripPayment) {

        $transaction = new Transactions();
        $transaction->setDatetime(date_create());
        $transaction->setName($tripPayment->getCustomer()->getName());
        $transaction->setSurname($tripPayment->getCustomer()->getSurname());
        $transaction->setEmail($tripPayment->getCustomer()->getEmail());
        $transaction->setAmount($tripPayment->getTotalCost());
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
