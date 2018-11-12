<?php

namespace SharengoCore\Service;

use SharengoCore\Entity\Repository\ExtraPaymentRatesRepository;
use SharengoCore\Entity\ExtraPaymentRates;
use SharengoCore\Entity\ExtraPayments;
use SharengoCore\Entity\Customers;

use Doctrine\ORM\EntityManager;

class ExtraPaymentRatesService
{
    /**
     * @var EntityManager
     */
    private $entityManager;
    
    /**
     * @var ExtraPaymentRatesRepository
     */
    private $extraPaymentRatesRepository;

    /**
     * @param EntityManager $entityManager
     */
    public function __construct(
        EntityManager $entityManager,
        ExtraPaymentRatesRepository $extraPaymentRatesRepository
    ) {
        $this->entityManager = $entityManager;
        $this->extraPaymentRatesRepository = $extraPaymentRatesRepository;
    }
    
    public function registerExtraPaymentRate(Customers $customer, $amountRate, $debit_date, $extraPaymentFather) {
        $extraPaymentRate = new ExtraPaymentRates( 
            $customer,
            $extraPaymentFather,
            $amountRate,
            $debit_date
        );

        $this->entityManager->persist($extraPaymentRate);
        $this->entityManager->flush();

        return $extraPaymentRate;
    }
    
    public function setPaymentRate(ExtraPaymentRates $extraPaymentRate, ExtraPayments $extraPayment){
        $extraPaymentRate->setExtraPayment($extraPayment);
        $this->entityManager->persist($extraPaymentRate);
        $this->entityManager->flush();
        return $extraPaymentRate;
    }

    public function findByExtraPaymentFather($extraPaymentId) {
        return $this->extraPaymentRatesRepository->findByExtraPaymentFather($extraPaymentId);
    }
    
    public function ratesPaidByExtraPaymentFather($extraPaymentId) {
        $reslut = $this->extraPaymentRatesRepository->ratesPaidByExtraPaymentFather($extraPaymentId)[0][1];
        return (is_null($reslut) ? 0 : $reslut);
    }
    
    public function getAllRateToBeCharged($date) {
        return $this->extraPaymentRatesRepository->getAllRateToBeCharged($date);
    }
    
    public function getExtraPaymentFather($extraPaymentId) {
        $result = $this->extraPaymentRatesRepository->getExtraPaymentFather($extraPaymentId);
        return (is_null($result) || !isset($result[0]['id'])) ? null : $result[0]['id'];
    }

}
