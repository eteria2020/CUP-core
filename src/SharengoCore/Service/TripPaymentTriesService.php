<?php

namespace SharengoCore\Service;

use SharengoCore\Entity\Repository\TripPaymentTriesRepository;
use SharengoCore\Entity\TripPaymentTries;
use SharengoCore\Entity\TripPayments;
use SharengoCore\Entity\AlreadySetFirstPaymentTryTsException;

use Cartasi\Entity\Transactions;

use Doctrine\ORM\EntityManager;

class TripPaymentTriesService
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var TripPaymentTriesRepository
     */
    private $tripPaymentTriesRepository;

    /**
     * @param EntityManager
     * @param TripPaymentTriesRepository
     */
    public function __construct(
        EntityManager $entityManager,
        TripPaymentTriesRepository $tripPaymentTriesRepository
    ) {
        $this->entityManager = $entityManager;
        $this->tripPaymentTriesRepository = $tripPaymentTriesRepository;
    }

    /**
     * @param TripPayments $tripPayment
     * @param Transactions $transaction
     * @param string $outcome
     */
    public function registerPaymentTry(TripPayments $tripPayment, Transactions $transaction, $outcome)
    {
        $tripPaymentTry = $this->tripPaymentTriesRepository->findTripPaymentTry($tripPayment, $transaction);

        if ($tripPaymentTry !== null) {
            $tripPaymentTry->setOutcome($outcome);
        } else {
            $tripPaymentTry = $this->generateTripPaymentTry($tripPayment, $transaction, $outcome);
        }

        $this->entityManager->persist($tripPaymentTry);
        $this->entityManager->flush();
    }

    /**
     * @param TripPayments $tripPayment
     * @param Transactions $transaction
     * @param string $outcome
     * @return TripPaymentTries
     */
    public function generateTripPaymentTry(TripPayments $tripPayment, Transactions $transaction, $outcome)
    {
        $tripPaymentTry = new TripPaymentTries($tripPayment, $outcome, $transaction);
        if (!$tripPayment->isFirstPaymentTryTsSet()) {
            $tripPayment->setFirstPaymentTryTs($tripPaymentTry->getTs());
        }
        return $tripPaymentTry;
    }
}
