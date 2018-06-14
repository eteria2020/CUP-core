<?php

namespace SharengoCore\Service;

use Cartasi\Service\CartasiCustomerPaymentsInterface;
use Doctrine\ORM\EntityManager;
use SharengoCore\Entity\Preauthorizations;
use SharengoCore\Entity\TripPayments;
use SharengoCore\Entity\TripPaymentTries;
use SharengoCore\Entity\Trips;
use SharengoCore\Entity\Customers;
use SharengoCore\Entity\Webuser;
use Cartasi\Entity\Transactions;
use SharengoCore\Entity\Repository\PreauthorizationsRepository;

class PreauthorizationsService
{
    /** @var EntityManager */
    private $entityManager;

    /**
     * @var PreauthorizationsRepository
     */
    private $preauthorizationsRepository;

    /**
     * @var CartasiCustomerPaymentsInterface
     */
    private $cartasiCustomerPayments;

    /**
     * @var integer
     */
    private $preauthorizationsAmount;

    /**
     * @param $entityManager EntityManager
     * @param $preauthorizationsRepository PreauthorizationsRepository
     * @param $paymentsService PaymentsService
     * @param $preauthorizationsAmount
     */
    public function __construct(
        EntityManager $entityManager,
        PreauthorizationsRepository $preauthorizationsRepository,
        CartasiCustomerPaymentsInterface $cartasiCustomerPayments,
        $preauthorizationsAmount
    ) {
        $this->entityManager = $entityManager;
        $this->preauthorizationsRepository = $preauthorizationsRepository;
        $this->cartasiCustomerPayments = $cartasiCustomerPayments;
        $this->preauthorizationsAmount = $preauthorizationsAmount;
    }

    /**
     * @param Trips $trip
     * @param Transactions|null $transaction
     * @param Customers $customer
     * @return Preauthorizations
     */
    public function generatePreauthorizations(Customers $customer, Trips $trip = null, Transactions $transaction = null)
    {
        $preauthorizations = new Preauthorizations($status = null, $statusFrom = null, $successfullyAt = null, $customer, $trip, $transaction);
        return $preauthorizations;
    }

    public function computeTrip(Preauthorizations $preauthorizations, TripPayments &$tripPayment){

        if($tripPayment->getTotalCost() == $this->preauthorizationsAmount){

            $this->payedSuccessfully($tripPayment);

            $preauthorizations->setStatus(Preauthorizations::STATUS_COMPLETED);
            $preauthorizations->setStatusFrom(date_create());
            $preauthorizations->setSuccessfullyAt(date_create());
            $this->savePreauthorizations($preauthorizations);

        }else if($tripPayment->getTotalCost() > $this->preauthorizationsAmount){
            //if the trip has a higher cost than the preauthorized
            if($preauthorizations->getTransaction() instanceof Transactions) {
                $amount = $tripPayment->getTotalCost() - $this->preauthorizationsAmount;
                $this->tryCustomerTripPayment($tripPayment->getCustomer(), $tripPayment, $amount, $preauthorizations);
            }
        }else if($tripPayment->getTotalCost() < $this->preauthorizationsAmount){
            //if the trip has a lower cost than the preauthorized
            if($preauthorizations->getTransaction() instanceof Transactions) {
                //only for authorizated payment
                $amount =  $tripPayment->getTotalCost(); //amount to be accounted
                $type = 'P';

                $this->tryCustomerTripRefund($tripPayment, $amount, $preauthorizations, $type);
            }
        }
    }

    private function savePreauthorizations(Preauthorizations $preauthorizations)
    {
        $this->entityManager->persist($preauthorizations);
        $this->entityManager->flush();
    }

    private function tryCustomerTripPayment(
        Customers $customer,
        TripPayments &$tripPayment,
        $amount,
        Preauthorizations $preauthorizations,
        Webuser $webuser = null
    ) {
//        $response = $this->cartasiCustomerPayments->sendPaymentRequest(
//            $customer,
//            $amount,
//            false
//        );
//
//        $this->entityManager->beginTransaction();
//
//        try {
//            $tripPaymentTry = $this->generateTripPaymentTry(
//                $tripPayment,
//                $response->getOutcome(),
//                $response->getTransaction(),
//                $webuser
//            );
            $now = date_create();
//            if ($response->getCompletedCorrectly()) {
//                $this->payedSuccessfully($tripPayment);
//                $preauthorizations->setStatus(Preauthorizations::STATUS_COMPLETED);
//
//                $preauthorizations->setStatusFrom($now);
//                $preauthorizations->setSuccessfullyAt($now);
//                $this->savePreauthorizations($preauthorizations);
//            } else {
//                //unpayableConsequences: disabled the customer?
                //$this->toBePayedPreaut($tripPayment);
                $preauthorizations->setStatus(Preauthorizations::STATUS_TO_BE_PAYED_CHANGE);
                $preauthorizations->setStatusFrom($now);
                $this->savePreauthorizations($preauthorizations);
//            }
//
//            $this->entityManager->persist($tripPaymentTry);
//            //$this->entityManager->flush();
//
//            $this->entityManager->commit();
//
//        } catch (\Exception $e) {
//            $this->entityManager->rollback();
//            //throw $e;
//        }
//
//        return $response;
    }

    private function tryCustomerTripRefund(
        TripPayments &$tripPayment,
        $amount,
        Preauthorizations $preauthorizations,
        $type,
        Webuser $webuser = null
    ) {
        $response = $this->cartasiCustomerPayments->sendRefundRequest($preauthorizations->getTransaction()->getId(), $tripPayment->getTrip()->getCustomer(), $amount , $type);

            $now = date_create();
            if ($response->getCompletedCorrectly()) {

                $preauthorizations->setStatus(Preauthorizations::STATUS_COMPLETED);

                $preauthorizations->setStatusFrom($now);
                $preauthorizations->setSuccessfullyAt($now);
                $this->savePreauthorizations($preauthorizations);
            } else {
                $this->payedSuccessfully($tripPayment);
                $preauthorizations->setStatus(Preauthorizations::STATUS_REFUND);
                $preauthorizations->setStatusFrom($now);
                $this->savePreauthorizations($preauthorizations);
            }
    }

    public function generateTripPaymentTry(TripPayments &$tripPayment, $outcome, Transactions $transaction = null, Webuser $webuser = null)
    {
        $tripPaymentTry = new TripPaymentTries($tripPayment, $outcome, $transaction, $webuser);
        if (!$tripPayment->isFirstPaymentTryTsSet()) {
            $tripPayment->setFirstPaymentTryTs($tripPaymentTry->getTs());
        }
        return $tripPaymentTry;
    }

    private function payedSuccessfully(TripPayments &$tripPayment)
    {
        $tripPayment->setStatus(TripPayments::STATUS_PAYED_CORRECTLY);
        $tripPayment->setPayedSuccessfullyAt(date_create());

        if(!$tripPayment->isFirstPaymentTryTsSet()){
            $tripPayment->setFirstPaymentTryTs(date_create());
        }
    }

    private function toBePayedPreaut(TripPayments &$tripPayment)
    {
        $tripPayment->setStatus(TripPayments::STATUS_TO_BE_PAYED); //STATUS_TO_BE_PAYED_PREAUT
    }

}