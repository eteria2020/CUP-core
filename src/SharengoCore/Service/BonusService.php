<?php

namespace SharengoCore\Service;

use SharengoCore\Entity\CustomersBonus as Bonus;
use SharengoCore\Entity\Trips;
use SharengoCore\Utils\Interval;
use SharengoCore\Entity\Repository\CustomersBonusRepository as BonusRepository;

use Doctrine\ORM\EntityManager;

class BonusService
{
    /**
     * @var EntityManager $entityManager
     */
    private $entityManager;

    /**
     * @var BonusRepository $bonusRepository
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

        $interval = new Interval($start, $end);

        if ($start > $end) {
            return null;
        }

        if ($interval->minutes() > $bonus->getResidual()) {
            $end = $start->add(\DateInterval::createFromDateString($bonus->getResidual().' minutes'));
            $interval = new Interval($start, $end);
        }

        return $interval;
    }
}
