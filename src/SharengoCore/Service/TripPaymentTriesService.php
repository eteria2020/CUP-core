<?php

namespace SharengoCore\Service;

use SharengoCore\Entity\Repository\TripPaymentTriesRepository;
use SharengoCore\Entity\TripPaymentTries;
use SharengoCore\Entity\TripPayments;
use SharengoCore\Entity\Webuser;
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
     * @param integer $id
     * @return TripPaymentTries
     */
    public function getById($id)
    {
        return $this->tripPaymentTriesRepository->findOneById($id);
    }

    /**
     * @param TripPayments $tripPayment
     * @param Transactions $transaction
     * @param string $outcome
     * @return TripPaymentTries
     */
    public function registerPaymentTry(TripPayments $tripPayment, Transactions $transaction, $outcome)
    {
        $tripPaymentTry = $this->tripPaymentTriesRepository->findTripPaymentTry($tripPayment, $transaction);

        if ($tripPaymentTry !== null) {
            $tripPaymentTry->setOutcome($outcome);
        } else {
            $tripPaymentTry = $this->generateTripPaymentTry($tripPayment, $outcome, $transaction);
        }

        $this->entityManager->persist($tripPaymentTry);
        $this->entityManager->flush();

        return $tripPaymentTry;
    }

    /**
     * @param TripPayments $tripPayment
     * @param string $outcome
     * @param Transactions|null $transaction
     * @param Webuser|null $webuser
     * @return TripPaymentTries
     */
    public function generateTripPaymentTry(TripPayments $tripPayment, $outcome, Transactions $transaction = null, Webuser $webuser = null)
    {
        $tripPaymentTry = new TripPaymentTries($tripPayment, $outcome, $transaction, $webuser);
        if (!$tripPayment->isFirstPaymentTryTsSet()) {
            $tripPayment->setFirstPaymentTryTs($tripPaymentTry->getTs());
        }
        return $tripPaymentTry;
    }

    /**
     * @param TripPayments $tripPayment
     * @return TripPaymentTries[]
     */
    public function getByTripPayment(TripPayments $tripPayment)
    {
        return $this->tripPaymentTriesRepository->findByTripPayment($tripPayment);
    }
}
