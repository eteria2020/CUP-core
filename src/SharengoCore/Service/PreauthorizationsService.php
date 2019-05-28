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
            if ($tripPayment->getTotalCost() == $preauthorizations->getTransaction()->getAmount()) { //$this->preauthorizationsAmount){

                $this->payedSuccessfully($tripPayment);

                $this->setPreautDone($preauthorizations);

            } elseif ($tripPayment->getTotalCost() > $preauthorizations->getTransaction()->getAmount()) {

                $this->toBePayed($tripPayment, $preauthorizations);

            } elseif ($tripPayment->getTotalCost() < $preauthorizations->getTransaction()->getAmount()) {
                //if the trip has a lower cost than the preauthorized

                $this->toBeRefund($tripPayment);
                $preauthorizations->setStatus(Preauthorizations::STATUS_REFUND);
                $this->savePreauthorizations($preauthorizations);
            }
    }

    private function savePreauthorizations(Preauthorizations $preauthorizations)
    {
        $this->entityManager->persist($preauthorizations);
        $this->entityManager->flush();
    }

    private function toBePayed(
        TripPayments &$tripPayment,
        Preauthorizations $preauthorizations
    ) {
                $preauthorizations->setStatus(Preauthorizations::STATUS_TO_BE_PAYED);
                $this->savePreauthorizations($preauthorizations);
    }

    private function tryCustomerTripRefund(
        Trips $trip,
        $amount,
        Preauthorizations $preauthorizations,
        $type,
        $avoidTripPayment = true
    ) {
        $response = $this->cartasiCustomerPayments->sendRefundRequest($preauthorizations->getTransaction()->getId(), $trip->getCustomer(), $amount , $type);
            if($response->getCompletedCorrectly()) {
                $preauthorizations->setStatus(Preauthorizations::STATUS_COMPLETED);
                $this->savePreauthorizations($preauthorizations);
                $tripPayment = $trip->getTripPayment();
                if(!$avoidTripPayment && $tripPayment instanceof TripPayments){
                    $tripPayment->setPayedCorrectly();

                    $this->entityManager->persist($tripPayment);
                    $this->entityManager->flush();
                }
            } else {
                $preauthorizations->setStatus(Preauthorizations::STATUS_REFUND);
                $this->savePreauthorizations($preauthorizations);
            }
    }

    public function generateTripPaymentTry(TripPayments &$tripPayment, $outcome, Transactions $transaction = null, Webuser $webuser = null)
    {
        $tripPaymentTry = new TripPaymentTries($tripPayment, $outcome, $transaction, $webuser);
        if(!$tripPayment->isFirstPaymentTryTsSet()) {
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
            if($preaut->getStatus() == Preauthorizations::STATUS_TO_BE_PAYED){
                $amount = $tripPayment->getTotalCost() - $preaut->getTransaction()->getAmount();
            }
        }
        return $amount;
    }

    private function setPreautDone(Preauthorizations $preauthorizations){
        $preauthorizations->setStatus(Preauthorizations::STATUS_COMPLETED);
        $this->savePreauthorizations($preauthorizations);
    }

    public function markPreautAsDone(TripPayments $tripPayments){
        $preauthorizations = $tripPayments->getTrip()->getPreauthorization();
        if($preauthorizations instanceof Preauthorizations){
            if($preauthorizations->getStatus() == Preauthorizations::STATUS_TO_BE_PAYED){
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

        if($refund instanceof Refunds){
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

    public function processPreautRefunds($tripPayments, $avoidEmails, $avoidCartasi, $avoidPersistance){
        foreach($tripPayments as $tripPayment){
            $preauthorization = $tripPayment->getTrip()->getPreauthorization();
            if($preauthorization instanceof Preauthorizations){
                if($preauthorization->getStatus() == Preauthorizations::STATUS_REFUND){
                    $transaction = $preauthorization->getTransaction();
                    if($transaction instanceof Transactions){
                        $this->tryCustomerTripRefund( $tripPayment->getTrip(), $tripPayment->getTotalCost(), $preauthorization, 'P', false);
                    }
                }
            }
        }
    }

}