<?php


namespace SharengoCore\Service;

use Doctrine\ORM\EntityManager;

use SharengoCore\Entity\Cars;
use SharengoCore\Entity\Trips;
use SharengoCore\Entity\Configurations;

use SharengoCore\Service\TripsService;

use SMSGatewayMe\Client\ApiClient;
use SMSGatewayMe\Client\Api\MessageApi;
use SMSGatewayMe\Client\Configuration;
use SMSGatewayMe\Client\Model\SendMessageRequest;


class SmsService
{

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var
     */
    private $config;

    /**
     * @var \SharengoCore\Service\SimpleLoggerService
     */
    private $logger;

    /**
     * @var
     */
    private $configurationsService;


    /**
     * @var array
     */
    private $smsDbConfigurations;

    /**
     * @var
     */
    private $smsConfig;

    /**
     * @var
     */
    private $smsGatewayMe;

    /**
     * @var TripsService
     */
    private $tripsService;

    /**
     * SmsService constructor.
     * @param EntityManager $entityManager
     * @param $config
     * @param SimpleLoggerService $logger
     * @param ConfigurationsService $configurationsService
     * @param \SharengoCore\Service\TripsService $tripsService
     */
    public function __construct(EntityManager $entityManager,
        $config,
        SimpleLoggerService $logger,
        ConfigurationsService $configurationsService,
        TripsService $tripsService)
    {
        $this->entityManager = $entityManager;
        $this->config = $config;
        $this->logger = $logger;

        $this->configurationsService = $configurationsService;
        $this->tripsService = $tripsService;

        $this->smsDbConfigurations = $configurationsService->getConfigurationsKeyValueBySlug(Configurations::SMS);
        $this->smsConfig = $this->config['sms'];
        $this->smsGatewayMe = $this->config['smsGatewayMe'];
    }


    /**
     * Send a SMS to $phoneNumber and message $message.
     * First, we try with API of SmsGatewayMe (sms via tablet), if it isn't selected or fail, we try Api SmsHosying.
     * It' possible to force a sms gateway specifing $forceGateway
     *
     * @param $phoneNumber
     * @param $message
     * @param $response
     * @param null $forceGateway
     * @return bool
     */
    public function sendSms($phoneNumber, $message, &$response, $forceGateway = null) {

        $result = false;
        $response = array();

        if(is_null($forceGateway)) {
            if($this->smsDbConfigurations["smsgatewayme"]=="true") {
                $result = $this->sendSmsByGatewayMe($phoneNumber, $message, $response);
                if(!$result) {
                    $result = $this->sendSmsBySmsHosting($phoneNumber, $message, $response);
                }
            } else {
                $result = $this->sendSmsBySmsHosting($phoneNumber, $message, $response);
            }
        } else {
            if($forceGateway=="smsgatewayme") {
                $result = $this->sendSmsByGatewayMe($phoneNumber, $message, $response);
            } elseif ($forceGateway=="smshosting") {
                $result = $this->sendSmsBySmsHosting($phoneNumber, $message, $response);
            }
        }

        return $result;

    }




    /**
     * Send sms via GatewayMe API
     *
     * https://github.com/smsgatewayme/client-php
     *
     * @param $phoneNumber
     * @param $message
     * @param $response
     * @return bool
     */
    private function sendSmsByGatewayMe($phoneNumber, $message, &$response) {
        $result = false;
        $response = array();
        $response['gateway'] = 'SMSGatewayMe';
        $response['phoneNumber'] = $phoneNumber;
        $response['message'] = $message;
        $response['ts_insert'] = date_create()->format('y-m-d H:i:s');

        try {
            $config = Configuration::getDefaultConfiguration();
            $config->setApiKey('Authorization', $this->smsGatewayMe["token"]);
            $apiClient = new ApiClient($config);
            $messageClient = new MessageApi($apiClient);

            // Sending a SMS Message
            $sendMessageRequest = new SendMessageRequest([
                'phoneNumber' => $phoneNumber,
                'message' => $message,
                'deviceId' => $this->smsGatewayMe["deviceId"]
            ]);

            $sendMessages = $messageClient->sendMessages([
                $sendMessageRequest,
            ]);

            $response['ts_send'] = date_create()->format('y-m-d H:i:s');

            if(isset($sendMessages[0])){
                if($sendMessages[0] instanceof \SMSGatewayMe\Client\Model\Message){
                    $response['response']=$sendMessages[0]->getId();
                    $result = true;
                } else {
                    $response['error'] = 'not instanceof';
                }
            } else {
                $response['error'] = 'not isset';
            }

        } catch (\Exception $e){
            $response['error'] = $e->getMessage();
        }

        $response['result'] = $result;
        return $result;
    }

    /**
     * Send sms via SmsHosting API
     *
     * https://help.smshosting.it/it/docs/sms-rest-api/invio
     *
     * @param $phoneNumber
     * @param $message
     * @param $response
     * @return bool
     */
    private function sendSmsBySmsHosting($phoneNumber, $message, &$response) {
        $result = false;
        $response = array();
        $response['gateway'] = 'SmsHosting';
        $response['phoneNumber'] = $phoneNumber;
        $response['message'] = $message;
        $response['ts_insert'] = date_create()->format('y-m-d H:i:s');

        try {
            $username = $this->smsConfig['username'];
            $password = $this->smsConfig['password'];
            $url = $this->smsConfig['url'];

            $fields = array(
                'to' => $phoneNumber,
                'from' => $this->smsConfig['from'],
                'text' => utf8_encode($message)
            );

            if(isset($this->smsConfig['sandbox'])) {
                $fields['sandbox'] = $this->smsConfig['sandbox'];
            }

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);

            curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($fields));

            $out = curl_exec($ch);
            $sms_msg = json_decode($out);
            curl_close($ch);

            $response['ts_send'] = date_create()->format('y-m-d H:i:s');
            $response['response']=$sms_msg;

            if (empty($out)) {
                $response['error'] = 'generic';

            } else {
                if (isset($sms_msg->errorCode)) {
                    $response['error'] = 'error '.$sms_msg->errorCode;
                } else {
                    $result = true;
                }
            }

        } catch (\Exception $e){
            $response['error'] = $e->getMessage();
        }

        $response['result'] = $result;
        return $result;

    }

    /**Trasform an SOS message on $tripId to a SMS
     *
     *
     * @param $carPalte
     * @param $response
     * @return bool
     */
    public function sendSosViaSms($tripId, &$response) {
        $result = false;
        $response = array();
        $countSuccess = 0;

        $trip = $this->tripsService->getById($tripId);

        if ($trip instanceof Trips) {
            $message = sprintf('SOS (%s) di %s %s, tel. %s, su auto %s ',
                date_create()->format('H:i:s'),
                $trip->getCustomer()->getName(),
                $trip->getCustomer()->getSurname(),
                $trip->getCustomer()->getMobile(),
                $trip->getCar()->getPlate());

            $phoneNumberArray = $this->getPhoneNumberFromConfigDb($trip->getCar());

            foreach ($phoneNumberArray as $phoneNumber) {
                if($this->sendSms($phoneNumber, $message, $smsResponse)) {
                    ++$countSuccess;
                } else {

                }
                $response[$phoneNumber] = $smsResponse;
            }

            if($countSuccess==count($phoneNumberArray)) {
                $result = true;
            }
        }

        return $result;
    }

    /**
     * Return a list of valid remote address.
     *
     * @return string
     */
    public function getValidIpFromConfigDb() {
        $result = "";

        try {
            $sosConfigArray = $this->configurationsService->getConfigurationsBySlug(Configurations::SOS);
            if (!is_null($sosConfigArray)) {
                $sosConfig = $sosConfigArray[0];
                if ($sosConfig instanceof Configurations) {
                    if ($sosConfig->getConfigValue()=='true') {
                        $sosConfigDecoded = json_decode($sosConfig->getConfigSpecific(), true);

                        if(isset($sosConfigDecoded['validIp'])) {
                            $result = $sosConfigDecoded['validIp'];
                        }
                    }
                }
            }
        } catch(\Exception $e) {

        }

        return $result;
    }


    /**
     * Return an array of phone number to send SMS.
     *
     * The data is read from database into table "configurations", with slug equals a 'sos'.
     * The phone must be in the same fleet_id of the car.
     *
     * @param Cars $car
     * @return array
     */
    private function getPhoneNumberFromConfigDb(Cars $car) {
        $result = array();

        try {
            $sosConfigArray = $this->configurationsService->getConfigurationsBySlug(Configurations::SOS);

            if (!is_null($sosConfigArray)) {
                $sosConfig = $sosConfigArray[0];
                if ($sosConfig instanceof Configurations) {
                    if ($sosConfig->getConfigValue()=='true') {
                        $sosConfigDecoded = json_decode($sosConfig->getConfigSpecific(), true);

                        if(isset($sosConfigDecoded['phoneBook'])) {
                            foreach($sosConfigDecoded['phoneBook'] as $row) {
                                if ($car->getFleet()->getId()== $row['fleetId']) {
                                    $result = $row['phoneNumber'];
                                    break;
                                }
                            }
                        }
                    }
                }
            }

        } catch(\Exception $e) {

        }

        return $result;
    }

}