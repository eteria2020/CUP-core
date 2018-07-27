<?php

namespace SharengoCore\Service;

use SharengoCore\Entity\Customers;
use SharengoCore\Entity\CustomersBonus as Bonus;
use SharengoCore\Entity\CustomersBonusPackages as BonusPackages;
use SharengoCore\Entity\Repository\CustomersBonusRepository as BonusRepository;
use SharengoCore\Entity\Trips;
use SharengoCore\Utils\Interval;

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

    /**
     * @param EntityManager $entityManager
     * @param BonusRepository $bonusRepository
     */
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
        $validFrom = $bonus->getValidFrom();
        $validTo = $bonus->getValidTo();

        if($bonus->getDescription() == Bonus::WOMEN_VOUCHER_DESCRIPTION ){
            $validFrom = date_create( date('Y-m-d').' 01:00:00');
            $validTo = date_create( date('Y-m-d').' 06:00:00');
        }

        $start = max($trip->getTimestampBeginning(), $validFrom);
        $end = min($trip->getTimestampEnd(), $validTo);

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
    
    /**
     * @param Customers $customer
     * @param integer $bonus_to_assign
     * @param string $bonus_type
     * @param string $description
     * @param string $validTo
     * @param string $valFrom
     * @return Bonus
     */
    public function createBonusForCustomerFromData(Customers $customer, $bonus_to_assign, $bonus_type, $description, $validTo, $valFrom)
    {
        $bonus = Bonus::createBonus($customer, $bonus_to_assign, $description, $validTo, $valFrom, $bonus_type);

        $this->entityManager->persist($bonus);
        $this->entityManager->flush();

        return $bonus;
    }

    /**
     * @param integer $id
     * @return CustomersBonus|null
     */
    public function getBonusFromId($id)
    {
        return $this->bonusRepository->findOneById($id);
    }

    /** 
     * @param Customers $customer
     * @param string $date_ts
     * @return Bonus
     */
    public function verifyBonusPoisAssigned(Customers $customer, $date_ts)
    {
        return $this->bonusRepository->getBonusPoisAssigned($customer, $date_ts);
    }
    
    /** 
     *  @param Customers $customer
     *  @return Bonus
     */
    public function verifyWomenBonusPackage($customer)
    {
        return $this->bonusRepository->getWomenBonusPackage($customer);
    }

    /**
     *  @param Customers $customer
     *  @return Bonus
     */
    public function verifyWelcomeBonusPackage($customer)
    {
        return $this->bonusRepository->getWelcomeBonusPackage($customer);
    }
    
}
