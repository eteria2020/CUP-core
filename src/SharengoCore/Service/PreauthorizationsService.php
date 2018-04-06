<?php

namespace SharengoCore\Service;

use Cartasi\Entity\Refunds;
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
//        if($preauthorizations->getStatus()==Preauthorizations::STATUS_COMPLETED){
//
//            if($tripPayment->getTotalCost() == $this->getPayedAmountPreaut($preauthorizations)) {
//                return;
//            } elseif ($tripPayment->getTotalCost() > $this->getPayedAmountPreaut($preauthorizations)){
//                $preauthorizations->setStatus(Preauthorizations::STATUS_TO_BE_PAYED);
//                $preauthorizations->setStatusFrom(date_create());
//                $this->savePreauthorizations($preauthorizations);
//            } elseif ($tripPayment->getTotalCost() < $this->getPayedAmountPreaut($preauthorizations)){
//                $preauthorizations->setStatus(Preauthorizations::STATUS_REFUND);
//                $preauthorizations->setStatusFrom(date_create());
//                $this->savePreauthorizations($preauthorizations);
//            }
//
//        } else {

            if ($tripPayment->getTotalCost() == $preauthorizations->getTransaction()->getAmount()) { //$this->preauthorizationsAmount){

                $this->payedSuccessfully($tripPayment);

                $preauthorizations->setStatusFrom(date_create());
                $this->setPreautDone($preauthorizations);

            } else if ($tripPayment->getTotalCost() > $preauthorizations->getTransaction()->getAmount()) {
                //if the trip has a higher cost than the preauthorized
                //if ($preauthorizations->getTransaction() instanceof Transactions) {
                    //$amount = $tripPayment->getTotalCost() - $preauthorizations->getTransaction()->getAmount();
                    $this->tryCustomerTripPayment($tripPayment, $preauthorizations);
                //}
            } else if ($tripPayment->getTotalCost() < $preauthorizations->getTransaction()->getAmount()) {
                //if the trip has a lower cost than the preauthorized

                    $this->toBeRefund($tripPayment);
                    $preauthorizations->setStatus(Preauthorizations::STATUS_REFUND);
                    $preauthorizations->setStatusFrom(date_create());
                    $this->savePreauthorizations($preauthorizations);
            }
        //}
    }

    private function savePreauthorizations(Preauthorizations $preauthorizations)
    {
        $this->entityManager->persist($preauthorizations);
        $this->entityManager->flush();
    }

    private function tryCustomerTripPayment(
        //Customers $customer,
        TripPayments &$tripPayment,
        //$amount,
        Preauthorizations $preauthorizations
        //Webuser $webuser = null
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
//            $now = date_create();
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
                $preauthorizations->setStatus(Preauthorizations::STATUS_TO_BE_PAYED);
                $preauthorizations->setStatusFrom(date_create());
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
        Trips $trip,
        $amount,
        Preauthorizations $preauthorizations,
        $type
    ) {
        $response = $this->cartasiCustomerPayments->sendRefundRequest($preauthorizations->getTransaction()->getId(), $trip->getCustomer(), $amount , $type);
            $now = date_create();
            if ($response->getCompletedCorrectly()) {
                $preauthorizations->setStatus(Preauthorizations::STATUS_COMPLETED);
                $preauthorizations->setStatusFrom($now);
                $preauthorizations->setSuccessfullyAt($now);
                $this->savePreauthorizations($preauthorizations);
            } else {
                $preauthorizations->setStatus(Preauthorizations::STATUS_REFUND);
                $preauthorizations->setStatusFrom(date_create());
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
    private function toBeRefund(TripPayments &$tripPayment){
        $tripPayment->setStatus(TripPayments::STATUS_TO_BE_REFUND);
    }

    public function calculateAmount(TripPayments $tripPayment){
        $amount = $tripPayment->getTotalCost();
        $preaut = $tripPayment->getTrip()->getPreauthorization();
        if($preaut instanceof Preauthorizations){
            if ($preaut->getStatus() == Preauthorizations::STATUS_TO_BE_PAYED){
                $amount = $tripPayment->getTotalCost() - $preaut->getTransaction()->getAmount();
            }
        }
        return $amount;
    }

    private function setPreautDone(Preauthorizations $preauthorizations){
        $preauthorizations->setStatus(Preauthorizations::STATUS_COMPLETED);
        $preauthorizations->setSuccessfullyAt(date_create());
        $this->savePreauthorizations($preauthorizations);
    }

    public function markPreautAsDone(TripPayments $tripPayments){
        $preauthorizations = $tripPayments->getTrip()->getPreauthorization();
        if($preauthorizations instanceof Preauthorizations){
            if($preauthorizations == Preauthorizations::STATUS_TO_BE_PAYED){
                $this->setPreautDone($preauthorizations);
            }
        }
    }

    public function getRefundsByPreauthorizations(Preauthorizations $preauthorizations){
        return $this->preauthorizationsRepository->findRefundbyPreauthorization($preauthorizations);
    }

    public function getPayedAmountPreaut(Preauthorizations $preauthorizations){
        $preauthAmount = $preauthorizations->getTransaction()->getAmount();
        $refundAmount = 0;
        $refund = $this->getRefundsByPreauthorizations($preauthorizations);

        if ($refund instanceof Refunds){
            $refundAmount = (int)$refund->getAmountOp();
        }
        return $preauthAmount - $refundAmount;
    }

    public function refundNotPayableTrip(Trips $trips){
        $this->tryCustomerTripRefund(
            $trips,
            $trips->getPreauthorization()->getTransaction()->getAmount(),
            $trips->getPreauthorization(),
            'R'
            );
    }

}