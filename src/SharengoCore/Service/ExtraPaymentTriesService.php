<?php

namespace SharengoCore\Service;

use SharengoCore\Entity\Repository\TripPaymentTriesRepository;
use SharengoCore\Entity\TripPaymentTries;
use SharengoCore\Entity\TripPayments;
use SharengoCore\Entity\Repository\ExtraPaymentTriesRepository;
use SharengoCore\Entity\ExtraPaymentTries;
use SharengoCore\Entity\ExtraPayments;
use SharengoCore\Entity\Webuser;
use SharengoCore\Entity\AlreadySetFirstPaymentTryTsException;

use Cartasi\Entity\Transactions;

use Doctrine\ORM\EntityManager;

class ExtraPaymentTriesService
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var ExtraPaymentTriesRepository
     */
    private $extraPaymentTriesRepository;

    /**
     * @param EntityManager
     * @param ExtraPaymentTriesRepository
     */
    public function __construct(
        EntityManager $entityManager,
        ExtraPaymentTriesRepository $extraPaymentTriesRepository
    ) {
        $this->entityManager = $entityManager;
        $this->extraPaymentTriesRepository = $extraPaymentTriesRepository;
    }

    /**
     * @param integer $id
     * @return ExtraPaymentTries
     */
    public function getById($id)
    {
        return $this->extraPaymentTriesRepository->findOneById($id);
    }

    /**
     * @param ExtraPayments $extraPayment
     * @param Transactions $transaction
     * @param string $outcome
     * @return ExtraPaymentTries
     */
    public function registerPaymentTry(ExtraPayments $extraPayment, Transactions $transaction, $outcome)
    {
        $extraPaymentTry = $this->extraPaymentTriesRepository->findExtraPaymentTry($extraPayment, $transaction);

        if ($extraPaymentTry !== null) {
            $extraPaymentTry->setOutcome($outcome);
        } else {
            $extraPaymentTry = $this->generateExtraPaymentTry($extraPayment, $outcome, $transaction);
        }

        $this->entityManager->persist($extraPaymentTry);
        $this->entityManager->flush();

        return $extraPaymentTry;
    }

    /**
     * @param ExtraPayments $extraPayment
     * @param string $outcome
     * @param Transactions|null $transaction
     * @param Webuser|null $webuser
     * @return ExtraPaymentTries
     */
    public function generateExtraPaymentTry(ExtraPayments $extraPayment, $outcome, Transactions $transaction = null, Webuser $webuser = null)
    {
        $extraPaymentTry = new ExtraPaymentTries($extraPayment, $outcome, $transaction, $webuser);//<-------- COSTRUTTORE
        if (!$extraPayment->isFirstPaymentTryTsSet()) {
            $extraPayment->setFirstPaymentTryTs($extraPaymentTry->getTs());
        }
        return $extraPaymentTry;
    }

    /**
     * @param TripPayments $tripPayment
     * @return TripPaymentTries[]
     */
    public function getByExtraPayment(ExtraPayments $extraPayment)
    {
        return $this->extraPaymentTriesRepository->findByTripPayment($extraPayment);
    }
}
