<?php

namespace SharengoCore\Service;

use SharengoCore\Entity\CustomersBonus as Bonus;
use SharengoCore\Entity\CustomersBonusPackages as BonusPackages;
use SharengoCore\Entity\Trips;
use SharengoCore\Entity\Customers;
use SharengoCore\Utils\Interval;
use SharengoCore\Entity\Repository\CustomersBonusRepository as BonusRepository;

use Doctrine\ORM\EntityManager;

class BonusService
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var BonusRepository
     */
    private $bonusRepository;

    public function __construct(
        EntityManager $entityManager,
        BonusRepository $bonusRepository
    ) {
        $this->entityManager = $entityManager;
        $this->bonusRepository = $bonusRepository;
    }

    /**
     * decreases bonus remaining minutes
     *
     * @param Bonus $bonus
     * @param int $minutes
     * @return Bonus
     * @throws \Exception
     */
    public function decreaseBonusMinutes(Bonus $bonus, $minutes)
    {
        $availableMinutes = $bonus->getResidual();

        if ($minutes > $availableMinutes) {
            throw new \Exception("Impossible to decrease $minutes minutes. $availableMinutes minutes remaining");
        }

        $bonus->setResidual($availableMinutes - $minutes);

        $this->entityManager->persist($bonus);
        $this->entityManager->flush();

        return $bonus;
    }

    /**
     * computes how many minutes of a bonus can be used for a trip
     *
     * @param Trips $trip
     * @param Bonus $bonus
     * @return Interval|null
     */
    public function usedInterval(Trips $trip, Bonus $bonus)
    {
        $start = max($trip->getTimestampBeginning(), $bonus->getValidFrom());
        $end = min($trip->getTimestampEnd(), $bonus->getValidTo());

        if ($start > $end) {
            return null;
        }

        $interval = new Interval($start, $end);

        if ($interval->minutes() > $bonus->getResidual()) {
            $tempStart = clone $start;
            $end = $tempStart->add(\DateInterval::createFromDateString($bonus->getResidual().' minutes'));
            $interval = new Interval($start, $end);
        }

        return $interval;
    }

    /**
     * @param Customers $customer
     * @param BonusPackages $bonusPackage
     * @return Bonus
     */
    public function createBonusForCustomerFromCode(Customers $customer, BonusPackages $bonusPackage)
    {
        $bonus = Bonus::createFromBonusPackage($customer, $bonusPackage);

        $this->entityManager->persist($bonus);
        $this->entityManager->flush();

        return $bonus;
    }

    public function getBonusFromId($id)
    {
        return $this->bonusRepository->findOneById($id);
    }
}
