<?php

namespace SharengoCore\Service;

use SharengoCore\Entity\Queries\RecapAvailableMonths;
use SharengoCore\Entity\Queries\RecapAvailableYears;
use SharengoCore\Entity\Queries\PayedBetween;
use SharengoCore\Entity\Queries\PayedFleetBetween;
use SharengoCore\Entity\Queries\PayedFleetYearBetween;
use Doctrine\ORM\EntityManager;

class RecapService
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em) {
        $this->em = $em;
    }

    /**
     * @return string[]
     */
    public function getAvailableMonths($isArray = false)
    {
        $query = new RecapAvailableMonths($this->em);
        
        if ($isArray) {
            $res = array();
            foreach ($query() as $q) {
                $res[] = $q['date'];
            }
            return $res;
        }
        
        return $query();
    }

    /**
     * @return string[]
     */
    public function getAvailableYears($isArray = false)
    {
        $query = new RecapAvailableYears($this->em);
        
        if ($isArray) {
            $res = array();
            foreach ($query() as $q) {
                $res[] = $q['date'];
            }
            return $res;
        }
        
        return $query();
    }
    
    /**
     * Returns incomes for last month's days
     * @param string $dateString
     * @return array[] in format day => [fleet => income]
     */
    public function getDailyIncomeForMonth($dateString)
    {
        // Create an interval to represent a month
        $interval = new \DateInterval('P1M');
        $start = date_create_from_format('m-Y-d H:i:s', $dateString . '-01 00:00:00');
        $end = clone($start);
        $end->add($interval);

        $query = new PayedBetween($this->em, $start, $end, 'DD-MM-YYYY');
        $incomes = $query();

        return $this->groupIncomesByDateFleet($incomes);
    }

    /**
     * Returns incomes for last month's days
     * @param string $dateString
     * @param integer $id_fleet
     * @return array[] in format day => [fleet => income]
     */
    public function getDailyIncomeForMonthFleet($dateString, $id_fleet)
    {
        // Create an interval to represent a month
        $interval = new \DateInterval('P1M');
        $start = date_create_from_format('m-Y-d H:i:s', $dateString . '-01 00:00:00');
        $end = clone($start);
        $end->add($interval);

        $query = new PayedFleetBetween($this->em, $start, $end, $id_fleet, 'DD-MM-YYYY');
        $incomes = $query();

        return $this->groupIncomesByDate($incomes);
    }
    
    /**
     * Returns incomes for last month's weeks
     * @return array[] in format week => [fleet => income]
     */
    public function getWeeklyIncome()
    {
        // Create an interval to represent a month
        $interval = new \DateInterval('P28D');
        $end = date_create('next monday midnight');
        $start = clone($end);
        $start->sub($interval);

        $query = new PayedBetween($this->em, $start, $end, 'IYYY-IW');
        $incomes = $query();

        return $this->groupIncomesByDateFleet($incomes);
    }

    /**
     * Returns incomes for last year's months
     * @return array[] in format month => [fleet => income]
     */
    public function getMonthlyIncome()
    {
        // Create an interval to represent a month
        $interval = new \DateInterval('P12M');
        $end = date_create('first day of next month midnight');
        $start = clone($end);
        $start->sub($interval);

        $query = new PayedBetween($this->em, $start, $end, 'YYYY-MM');
        $incomes = $query();

        return $this->groupIncomesByDateFleet($incomes);
    }
    
    /**
     * Returns incomes for last year's months
     * @return array[] in format month => [fleet => income]
     */
    public function getMonthlyIncomeFleet($id_fleet)
    {
        // Create an interval to represent a month
        $interval = new \DateInterval('P12M');
        $end = date_create('first day of next month midnight');
        $start = clone($end);
        $start->sub($interval);

        $query = new PayedFleetBetween($this->em, $start, $end, $id_fleet, 'YYYY-MM');
        $incomes = $query();

        return $this->groupIncomesByDate($incomes);
    }

    /**
     * Returns incomes for last year's months
     * @return array[] in format month => [fleet => income]
     */
    public function getMonthlyIncomeFleetYear($id_fleet, $year)
    {
        if ($year == 0) {
            return $this->getMonthlyIncomeFleet($id_fleet);
        }

        $query = new PayedFleetYearBetween($this->em, $year, $id_fleet, 'YYYY-MM');
        $incomes = $query();

        return $this->groupIncomesByDate($incomes);
    }
    
    /**
     * @param array[] $incomes
     * @return array[]
     */
    private function groupIncomesByDateFleet($incomes)
    {
        $groupedIncomes = [];
        foreach ($incomes as $income) {
            $date = $income['date'];
            if (!array_key_exists($date, $groupedIncomes)) {
                $groupedIncomes[$date] = [];
            }
            $groupedIncomes[$date][$income['fleet']] = $income['amount'];
        }
        return $groupedIncomes;
    }
    
    /**
     * @param array[] $incomes
     * @return array[]
     */
    private function groupIncomesByDate($incomes)
    {
        
        $groupedIncomes = [];
        foreach ($incomes as $income) {
            $date = $income['date'];
            if (!array_key_exists($date, $groupedIncomes)) {
                $groupedIncomes[$date] = [];
            }
            $groupedIncomes[$date]['amount'] = $income['amount'];
            $groupedIncomes[$date]['tp_amount'] = $income['tp_amount'];
            $groupedIncomes[$date]['sp_amount'] = $income['sp_amount'];
            $groupedIncomes[$date]['ep_amount'] = $income['ep_amount'];
            $groupedIncomes[$date]['bpp_amount'] = $income['bpp_amount'];
            $groupedIncomes[$date]['amount'] = $income['amount'];
        }
        
        return $groupedIncomes;
    }
}
