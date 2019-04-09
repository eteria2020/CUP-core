<?php

namespace SharengoCore\Entity\Repository;

use SharengoCore\Entity\Customers;
use SharengoCore\Entity\PromoCodes;
use SharengoCore\Entity\Trips;
use SharengoCore\Service\PromoCodesService;
use SharengoCore\Entity\CustomersBonus;

/**
 * Class CustomersBonusRepository
 * @package SharengoCore\Entity\Repository
 */
class CustomersBonusRepository extends \Doctrine\ORM\EntityRepository
{
    /**
     * returns the bonuses appliable to a given trip
     *
     * @var Trips $trip
     * @return CustomersBonus[]
     */
    public function getBonusesForTrip(Trips $trip)
    {
        $em = $this->getEntityManager();
        $dql = 'SELECT cb '.
            'FROM \SharengoCore\Entity\CustomersBonus cb '.
            'WHERE cb.active = true '.
            'AND cb.description != :womenDescription '.
            'AND cb.validFrom <= :tripEnd '.
            'AND cb.validTo >= :tripBeginning '.
            'AND cb.residual > 0 '.
            'AND cb.customer = :customer '.
            'ORDER BY cb.validTo ASC';

        $query = $em->createQuery($dql);
        $query->setParameter('customer', $trip->getCustomer());
        $query->setParameter('tripBeginning', $trip->getTimestampBeginning());
        $query->setParameter('tripEnd', $trip->getTimestampEnd());
        $query->setParameter('womenDescription', CustomersBonus::WOMEN_VOUCHER_DESCRIPTION);

        return $query->getResult();
    }

    /**
     * returns the bonuses appliable to a given trip
     *
     * @var Trips $trip
     * @return CustomersBonus[]
     */
    public function getWomenBonusesForTrip(Trips $trip)
    {
        $em = $this->getEntityManager();
        $dql = 'SELECT cb '.
            'FROM \SharengoCore\Entity\CustomersBonus cb '.
            'WHERE cb.active = true '.
            'AND cb.description = :womenDescription '.
            'AND cb.validFrom <= :tripEnd '.
            'AND cb.validTo >= :tripBeginning '.
            'AND cb.residual > 0 '.
            'AND cb.customer = :customer '.
            'ORDER BY cb.validTo ASC';

        $query = $em->createQuery($dql);
        $query->setParameter('customer', $trip->getCustomer());
        $query->setParameter('tripBeginning', $trip->getTimestampBeginning());
        $query->setParameter('tripEnd', $trip->getTimestampEnd());
        $query->setParameter('womenDescription', CustomersBonus::WOMEN_VOUCHER_DESCRIPTION);

        return $query->getResult();
    }

    public function checkUsedPromoCode(Customers $customer, PromoCodes $promoCode)
    {
        $dql = 'SELECT cb FROM \SharengoCore\Entity\CustomersBonus cb ' .
                   'WHERE cb.customer = :id AND cb.promocode = :code';

        $query = $this->getEntityManager()->createQuery($dql);
        $query->setParameters([
            'id'   => $customer->getId(),
            'code' => $promoCode->getId()
        ]);

        return $query->getResult();
    }

    /**
     * returns the bonuses for customer, date and type
     * @param Customers $customer
     * @param DateTime $date
     * @param string $type
     * @return CustomersBonus[]
     */
    public function getBonusesForCustomerIdAndDateInsertionAndType(Customers $customer, \DateTime $date, $type)
    {
        $em = $this->getEntityManager();
        $dql = 'SELECT cb FROM \SharengoCore\Entity\CustomersBonus cb '.
            'WHERE cb.validFrom >= :date1 '.
            'AND cb.validFrom < :date2 '.
            'AND cb.type = :type '.
            'AND cb.customer = :customer';

        $date1 = $date->format('Y-m-d');
        $dateTo = new \DateTime($date1);
        $date2 = $dateTo->add(new \DateInterval('P1D'))->format('Y-m-d');

        $query = $em->createQuery($dql);
        $query->setParameter('date1', $date1);
        $query->setParameter('date2', $date2);
        $query->setParameter('type', $type);
        $query->setParameter('customer', $customer);

        return $query->getResult();
    }

    /**
     * 
     * @param Customers $customer
     * @param type $date_ts
     * @return type
     */
    public function getBonusPoisAssigned(Customers $customer, $date_ts)
    {
        $dql =  "SELECT cb FROM \SharengoCore\Entity\CustomersBonus cb " .
            "WHERE cb.insertTs >= :date_ts_start AND cb.insertTs <= :date_ts_end " .
            "AND cb.customer = :customer " .
            "AND cb.type LIKE 'zone-POIS%'";

        $query = $this->getEntityManager()->createQuery($dql);
        $query->setParameter('date_ts_start',  $date_ts . ' 00:00:00');
        $query->setParameter('date_ts_end',  $date_ts . ' 23:59:59');
        $query->setParameter('customer', $customer);
        return $query->getResult();
    }

    public function getWomenBonusPackage($customer) {
        $now = date("Y-m-d");
        $timeStart = date_create($now. "00:00:00");
        $timeEnd = date_create($now. "23:59:59");
        
        $em = $this->getEntityManager();
        $dql = "SELECT cb FROM \SharengoCore\Entity\CustomersBonus cb ".
                "WHERE cb.insertTs >= :timeStart AND cb.insertTs <= :timeEnd ".
                "AND cb.customer = :customer AND cb.description = :womenDescription ";
        $query = $em->createQuery($dql);
        $query->setParameter('timeStart', $timeStart);
        $query->setParameter('timeEnd', $timeEnd);
        $query->setParameter('customer', $customer);
        $query->setParameter('womenDescription', CustomersBonus::WOMEN_VOUCHER_DESCRIPTION);
        
        return $query->getResult();
    }

    public function getWelcomeBonusPackage($customer) {
        $em = $this->getEntityManager();
        $dql = "SELECT cb FROM \SharengoCore\Entity\CustomersBonus cb ".
               "WHERE cb.customer = :customer AND cb.description = 'Pacchetto di benvenuto' ";
        $query = $em->createQuery($dql);
        $query->setParameter('customer', $customer);
        return $query->getResult();
    }

    public function getSilverListBonus($customer, $start = null, $end = null) {
        $em = $this->getEntityManager();
        $dql = "SELECT cb FROM \SharengoCore\Entity\CustomersBonus cb ".
            "WHERE cb.customer = :customer AND cb.description = 'Bonus Silver List' ";
        if(!is_null($start)){
            $dql .= "AND cb.validFrom >= :validFrom ";
        }
        if(!is_null($end)){
            $dql .= "AND cb.validTo <= :validTo";
        }
        $query = $em->createQuery($dql);
        $query->setParameter('customer', $customer);
        if(!is_null($start)){
            $query->setParameter('validFrom', $start);
        }
        if(!is_null($end)){
            $query->setParameter('validTo', $end);
        }

        return $query->getResult();
    }

    public function getBonusFromACICard($card) {
        $em = $this->getEntityManager();
        $dql = "SELECT cb FROM \SharengoCore\Entity\CustomersBonus cb ".
            "WHERE cb.description LIKE :card ";
        $query = $em->createQuery($dql);
        $query->setParameter('card', '%'.strtoupper($card).'%');
        return $query->getResult();
    }
    
    public function getCustomerBonusNivea($descriptionBonusNivea) {
        $em = $this->getEntityManager();
        $dql = "SELECT c " .
                "FROM \SharengoCore\Entity\Customers c " .
                "WHERE 1=1 " .
                "AND c.insertedTs >= :start " .
                "AND c.insertedTs  < :end " .
                "AND c.id IN ( " .
                    "SELECT cu.id " .
                    "FROM \SharengoCore\Entity\Trips t " .
                    "JOIN \SharengoCore\Entity\Customers cu WITH t.customer = cu.id " .
                    "WHERE t.timestampBeginning >= :start " .
                    "AND t.timestampBeginning < :end ".
                    "AND t.fleet = :fleet " .
                    ") " .
                "AND c.id NOT IN ( ". 
                    "SELECT cus.id " .
                    "FROM \SharengoCore\Entity\CustomersBonus cb " .
                    "JOIN \SharengoCore\Entity\Customers cus WITH cb.customer = cus.id " .
                    "WHERE cb.description = :description " .
                    ") ";
        
        $query = $em->createQuery($dql);
        
        $query->setParameter('start', '2018-02-01 00:00:00');
        $query->setParameter('end', '2018-05-01 00:00:00');
        $query->setParameter('fleet', 1);
        $query->setParameter('description', $descriptionBonusNivea);
        
        return $query->getResult();
    }
    
    public function getCustomerBonusAlgebris($descriptionBonusNivea, $startMonth, $endMonth) {
        $em = $this->getEntityManager();
        $dql = "SELECT c " .
                "FROM \SharengoCore\Entity\Customers c  " .
                "WHERE 1=1  " .
                "AND c.fleet = :fleet " .
                "AND c.id NOT IN (  " .
                                "SELECT cus.id  " .
                                "FROM \SharengoCore\Entity\CustomersBonus cb  " .
                                "JOIN \SharengoCore\Entity\Customers cus WITH cb.customer = cus.id  " .
                                "WHERE cb.description = :description  " .
                                ") " .
                "AND c.id  IN (  " .
                                "SELECT cust.id " .
                                "FROM \SharengoCore\Entity\Trips ti " .
                                "JOIN \SharengoCore\Entity\Customers cust WITH ti.customer = cust.id " .
                                "WHERE ti.timestampBeginning >= :startMonth " .
                                "AND ti.timestampBeginning < :endMonth " .
                                "AND ti.fleet = :fleet " .
                                "AND ti.payable = :payable " .
                                "GROUP BY cust.id " .
                                "HAVING count(cust)>=3 " .
                                ") ";
        
        $query = $em->createQuery($dql);
        
        $query->setParameter('fleet', 1);
        $query->setParameter('payable', "TRUE");
        $query->setParameter('startMonth', $startMonth);
        $query->setParameter('endMonth', $endMonth);
        $query->setParameter('description', $descriptionBonusNivea);
        
        return $query->getResult();
    }
    
    public function checkIfCustomerRunBeforeDate(Customers $customer, $date_zero) {
        $em = $this->getEntityManager();
        $dql = "SELECT COUNT(t.id)  " .
                "FROM \SharengoCore\Entity\Trips t  " .
                "WHERE t.timestampBeginning < :dateZero " .
                "AND t.customer = :customer " ;
        
        $query = $em->createQuery($dql);
        
        $query->setParameter('dateZero', $date_zero); 
        $query->setParameter('customer', $customer); 
        
        return $query->getResult();
    }

    public function getNotRunningBonusPackage($customer) {
        $em = $this->getEntityManager();
        $dql = "SELECT cb FROM \SharengoCore\Entity\CustomersBonus cb ".
            "WHERE cb.customer = :customer AND cb.type = 'notRunning' ";
        $query = $em->createQuery($dql);
        $query->setParameter('customer', $customer);
        return $query->getResult();
    }

}
