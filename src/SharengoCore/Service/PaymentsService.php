<?php

namespace SharengoCore\Service;

use Cartasi\Service\CartasiCustomerPaymentsInterface;
use Cartasi\Service\CartasiContractsService;
use SharengoCore\Entity\Preauthorizations;
use SharengoCore\Entity\Repository\FreeFaresRepository;
use SharengoCore\Entity\Reservations;
use SharengoCore\Service\TripPaymentTriesService;
use SharengoCore\Service\CustomerDeactivationService;
use SharengoCore\Service\FreeFaresService as FreeFares;
use SharengoCore\Service\Partner\TelepassPayService;
use SharengoCore\Service\Partner\NugoPayService;
use SharengoCore\Entity\Repository\TripsRepository;
use SharengoCore\Entity\Repository\ReservationsRepository;
use SharengoCore\Entity\Customers;
use SharengoCore\Entity\Trips;
use SharengoCore\Entity\TripPayments;
use SharengoCore\Entity\Webuser;
use SharengoCore\Entity\TripPaymentTries;
use SharengoCore\Entity\ExtraPayments;
use SharengoCore\Entity\ExtraPaymentTries;

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
     * @var ExtraPaymentTriesService
     */
    private $extraPaymentTriesService;

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
     * @var PreauthorizationsService
     */
    private $preauthorizationsService;

    /**
     * @var integer
     */
    private $preauthorizationsAmount;

    /**
     * @var FreeFaresRepository
     */
    private $freeFaresRepository;

    /**
     * @var TripsRepository
     */
    private $tripsRepository;

    /**
     * @var ReservationsRepository
     */
    private $reservationsRepository;

     /**
     * @var TelepassPayService
     */
    private $telepassPayService;

     /**
     * @var NugoPayService
     */
    private $nugoPayService;

    /**
     * @param CartasiCustomerPaymentsInterface $cartasiCustomerPayments
     * @param CartasiContractsService $cartasiContractService
     * @param EntityManager $entityManager
     * @param EmailService $emailService
     * @param EventManager $eventManager
     * @param TripPaymentTriesService $tripPaymentTriesService
     * @param ExtraPaymentTriesService $extraPaymentTriesService
     * @param string $url
     * @param CustomerDeactivationService $deactivationService
     * @param PreauthorizationsService $preauthorizationsService
     * @param int $preauthorizationsAmount
     * @param FreeFaresRepository $freeFaresRepository
     * @param TripsRepository $tripsRepository
     * @param ReservationsRepository $reservationsRepository
     * @param TelepassPayService $telepassPayService
     * @param NugoPayService $nugoPayService
     *
     */
    public function __construct(
        CartasiCustomerPaymentsInterface $cartasiCustomerPayments,
        CartasiContractsService $cartasiContractService,
        EntityManager $entityManager,
        EmailService $emailService,
        EventManager $eventManager,
        TripPaymentTriesService $tripPaymentTriesService,
        ExtraPaymentTriesService $extraPaymentTriesService,
        $url,
        CustomerDeactivationService $deactivationService,
        PreauthorizationsService $preauthorizationsService,
        $preauthorizationsAmount,
        FreeFaresRepository $freeFaresRepository,
        TripsRepository $tripsRepository,
        ReservationsRepository $reservationsRepository,
        TelepassPayService $telepassPayService,
        NugoPayService $nugoPayService

    ) {
        $this->cartasiCustomerPayments = $cartasiCustomerPayments;
        $this->cartasiContractService = $cartasiContractService;
        $this->entityManager = $entityManager;
        $this->emailService = $emailService;
        $this->eventManager = $eventManager;
        $this->tripPaymentTriesService = $tripPaymentTriesService;
        $this->extraPaymentTriesService = $extraPaymentTriesService;
        $this->url = $url;
        $this->deactivationService = $deactivationService;
        $this->preauthorizationsService = $preauthorizationsService;
        $this->preauthorizationsAmount = $preauthorizationsAmount;
        $this->freeFaresRepository = $freeFaresRepository;
        $this->tripsRepository = $tripsRepository;
        $this->reservationsRepository = $reservationsRepository;
        $this->telepassPayService = $telepassPayService;
        $this->nugoPayService = $nugoPayService;
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

        //$this->clearEntityManager();
    }
    
    /**
     * tries to pay the extraPayment, checking first if the extra can be payed and
     * otherwise sending a payment request to the customer
     *
     * @param ExtraPayments $extraPayment
     */
    public function tryPaymentExtra(
        ExtraPayments $extraPayment,
        $avoidEmail = false,
        $avoidCartasi = false,
        $avoidPersistance = false
    ) {
        $this->avoidEmail = $avoidEmail;
        $this->avoidCartasi = $avoidCartasi;
        $this->avoidPersistance = $avoidPersistance;

        $customer = $extraPayment->getCustomer();

        if ($this->cartasiContractService->hasCartasiContract($customer)) {
            $this->tryCustomerExtraPayment(
                $customer,
                $extraPayment
            );
        } else {
            if ($customer->getPaymentAble()) {
                // enable hooks on the event that the customer doesn't have a valid contract
                $this->eventManager->trigger('notifyCustomerPayExtra', $this, [
                    'customer' => $customer,
                    'extraPayment' => $extraPayment
                ]);
            }

            $this->disableCustomer($customer);
        }

        //$this->clearEntityManager();
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
     * tries to pay the extra amount
     * writes in database a record in the extra_payment_tries table
     *
     * @param TripPayments $extraPayment
     * @param Webuser $webuser
     * @param boolean $avoidEmail
     * @param boolean $avoidCartasi
     * @param boolean $avoidPersistance
     * @param boolean $avoidDisableUser
     * @return CartasiResponse
     */
    public function tryExtraPayment(
        ExtraPayments $extraPayment,
        Webuser $webuser,
        $avoidEmail = false,
        $avoidCartasi = false,
        $avoidPersistance = false,
        $avoidDisableUser = false
    ) {
        $this->avoidEmail = $avoidEmail;
        $this->avoidCartasi = $avoidCartasi;
        $this->avoidPersistance = $avoidPersistance;

        $customer = $extraPayment->getCustomer();

        return $this->tryCustomerExtraPayment(
            $customer,
            $extraPayment,
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

        $contract = $this->cartasiContractService->getCartasiContract($customer);

        if(!is_null($contract->getPartner())) { // contract width partner
            $response = null;
            if($contract->getPartner()->getCode()=='telepass') {
                $response = $this->telepassPayService->sendPaymentRequest(
                    $customer,
                    $tripPayment->getTotalCost(),
                    $this->avoidCartasi
                );
            } elseif ($contract->getPartner()->getCode()=='nugo') {
                $response = $this->nugoPayService->sendTripPaymentRequest(
                    $tripPayment,
                    $this->avoidCartasi
                );
            }

            if(is_null($response)) {
                return $response;
            }
        } else {
            $response = $this->cartasiCustomerPayments->sendPaymentRequest(
                $customer,
                $this->preauthorizationsService->calculateAmount($tripPayment),
                $this->avoidCartasi
            );
        }

        $this->entityManager->beginTransaction();

        try {
            $tripPaymentTry = $this->tripPaymentTriesService->generateTripPaymentTry(
                $tripPayment,
                $response->getOutcome(),
                $response->getTransaction(),
                $webuser
            );

            if ($response->getCompletedCorrectly()) {
                $this->updateTripPaymentPartner($contract, $tripPayment);
                $this->markTripAsPayed($tripPayment);

                //if we have done a preauthorization and its status is 'to be payed'
                $this->preauthorizationsService->markPreautAsDone($tripPayment);
            } else {
                $this->unpayablePaymentConsequences(
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
     * tries to pay the extra amount
     * writes in database a record in the extra_payment_tries table
     *
     * @param Customers $customer
     * @param TripPayments $tripPayment
     * @param Webuser|null $webuser
     * @param boolean $avoidDisableUser
     * @return CartasiResponse
     */
    private function tryCustomerExtraPayment(
        Customers $customer,
        ExtraPayments $extraPayment,
        Webuser $webuser = null,
        $avoidDisableUser = false
    ) {
        $response = $this->cartasiCustomerPayments->sendPaymentRequest(
            $customer,
            $extraPayment->getAmount(),
            $this->avoidCartasi
        );

        $this->entityManager->beginTransaction();

        try {
            $extraPaymentTry = $this->extraPaymentTriesService->generateExtraPaymentTry(
                $extraPayment,
                $response->getOutcome(),
                $response->getTransaction(),
                $webuser
            );

            if ($response->getCompletedCorrectly()) {
                $this->markExtraAsPayed($extraPayment);
            } else {
                $this->unpayableExtraConsequences(
                        $customer,
                        $extraPayment,
                        $extraPaymentTry,
                        $avoidDisableUser
                );
            }

            $this->entityManager->persist($extraPaymentTry);
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
     * @return CartasiResponse
     */
    public function tryTripPaymentMulti(
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
             $totalCost += $this->preauthorizationsService->calculateAmount($trip->getTripPayment());//$trip->getTripPayment()->getTotalCost();
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

            foreach ($trips as $trip) {
                $tripPayment = $trip->getTripPayment();
                $tripPaymentTry = $this->tripPaymentTriesService->generateTripPaymentTry(
                    $tripPayment,
                    $response->getOutcome(),
                    $response->getTransaction(),
                    $webuser
                );

                if ($response->getCompletedCorrectly()) {
                    $this->markTripAsPayed($tripPayment);
                    $this->preauthorizationsService->markPreautAsDone($tripPayment);
                } else {
//                    $this->unpayableConsequences(
//                        $customer,
//                        $tripPayment,
//                        $tripPaymentTry,
//                        $avoidDisableUser
//                    );
                }

                $this->entityManager->persist($tripPaymentTry);
                $this->entityManager->flush();
            }

            if ($response->getCompletedCorrectly()) {
                $this->deactivationService->reactivateCustomerForFailedPayment(
                    $customer,
                    null,
                    new \DateTime(),
                    true);
                // set preauthorizations as done
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
     * @param ExtraPayments $extraPayment
     */
    private function markExtraAsPayed(ExtraPayments $extraPayment)
    {
        $extraPayment->setPayedCorrectly();
        $extraPayment->setInvoiceAble(true);

        $this->entityManager->persist($extraPayment);
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
    private function unpayablePaymentConsequences(
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

    /**
     * If the extra of a trip does not complete correctly we:
     * - disable the customer
     * - extra payment set as not payed
     * - send mail to notify customer
     *
     * @param Customers $customer
     * @param ExtraPayments $extraPayment
     * @param ExtraPaymentTries $extraPaymentTry
     * @param boolean $avoidDisableUser
     */
    private function unpayableExtraConsequences(
        Customers $customer,
        ExtraPayments $extraPayment,
        ExtraPaymentTries $extraPaymentTry,
        $avoidDisableUser
    ) {
        // disable the customer
        if (!$avoidDisableUser) {
            $this->deactivationService->deactivateForExtraPaymentTry(
                $customer,
                $extraPaymentTry
            );
        }
        $customer->setPaymentAble(false);

        $this->entityManager->persist($customer);

        // set the extra payment as wrong payment
        $extraPayment->setWrongExtra();

        $this->entityManager->persist($extraPayment);
        $this->entityManager->flush();

        
        // other unpayable consequences not mentionable here for respect of the childrens
        $this->eventManager->trigger('wrongExtraPayment', $this, [
            'customer' => $customer,
            'extraPayment' => $extraPayment
        ]);
        
    }
    
    public function tryPreAuthorization(Customers $customer, Trips $trip, $avoidEmails = false, $avoidCartasi = false, $avoidPersistance = false){

        $message = 22; //default ok
        //TODO: check other preauthorization for same customer and car plate
        if ($customer->getResidualBonuses() >= 20){
            return $message = 23;
        }

        $pin = json_decode($customer->getPin(), true);
        if (isset($pin["company"]) && !is_null($pin["company"]) && isset($pin["companyPinDisabled"]) && $pin["companyPinDisabled"] == false) {
            return $message = 25; //maybe business user
        }

        if ($this->verifyFreeFaresPreauth($trip)){
            return $message = 24; //freefares
        }

        if (!$this->cartasiContractService->hasCartasiContract($customer)){
            return $message = 26; //the customer doesn't have a contract
        }

        $response = $this->cartasiCustomerPayments->sendPaymentRequest(
            $customer,
            $this->preauthorizationsAmount,
            $avoidCartasi
        );

        $this->entityManager->beginTransaction();

        try {
            $preauthorization = $this->preauthorizationsService->generatePreauthorizations(
                $customer,
                $trip,
                $response->getTransaction()
            );

            if ($response->getCompletedCorrectly()){
                $message = 22; // successfully
                $this->entityManager->persist($preauthorization);
                $this->entityManager->flush();
            } else {
                $message = 27; // failed transaction
            }

            if (!$avoidPersistance) {
                $this->entityManager->commit();
            } else {
                $this->entityManager->rollback();
            }
        } catch (\Exception $e) {
            $this->entityManager->rollback();
            $message = 20; //exception
            throw $e;
        }

        return $message;
    }

    /**
     * @param Trips $trip
     * @return bool
     */

    private function verifyFreeFaresPreauth(Trips $trip) {
        //verify free15
        $freeFares = $this->freeFaresRepository->findAllActive();
        $carConditions = [];
        foreach ($freeFares as $freeFare) {
            $conditions = json_decode($freeFare->getConditions(), true);
            if(isset($conditions['car'])) {
                $carConditions = $conditions['car'];
                if ($carConditions['type'] == 'nouse') {
                    return FreeFares::verifyFilterCar($trip, $carConditions, $this->tripsRepository, $this->reservationsRepository);
                } else {
                    return false;
                }
            } else {
                return false;
            }
        }
    }

    public function refund($transaction, $customer, $amount) {
        return $this->cartasiCustomerPayments->sendRefundRequest($transaction, $customer, $amount);
    }

    /**
     * Clear EntityManager every 50 call, and return tru if it's happen.
     * @staticvar int $countClear
     * @return boolean
     */
    private function clearEntityManager() {
        $result = false;

        $this->entityManager->clear("SharengoCore\Entity\Trips");
        $this->entityManager->clear("SharengoCore\Entity\TripPayments");
        $this->entityManager->clear("SharengoCore\Entity\TripPaymentTries");

//        $this->entityManager->clear("SharengoCore\Entity\Cars");
//        $this->entityManager->clear("SharengoCore\Entity\Customers");

//        static $countClear = 50;
//
//        if($countClear<=0) {
//            $this->entityManager->clear();
//            $countClear = 50;
//            $result = true;
//        } else {
//            --$countClear;
//        }

        return $result;
    }

    /**
     * Update the tripPaiments with partner
     * 
     * @param Customers $customer
     * @param TripPayments $tripPayment
     * @param type $avoidPersistance
     */
    private function updateTripPaymentPartner(Contract $contract, TripPayments $tripPayment) {

        if(!$this->avoidPersistance) {
            if(!is_null($contract->getPartner())) {
                $tripPayment->setPartner($contract->getPartner());
            }
        }
    }
}
