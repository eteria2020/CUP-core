<?php

namespace SharengoCore\Entity\Repository;

use SharengoCore\Entity\Customers;
use SharengoCore\Entity\PromoCodes;
use SharengoCore\Entity\CustomersPoints;
use SharengoCore\Entity\Trips;
use SharengoCore\Service\PromoCodesService;

/**
 * Class CustomersPointsRepository
 * @package SharengoCore\Entity\Repository
 */
class CustomersPointsRepository extends \Doctrine\ORM\EntityRepository
{
    public function getCustomersRunYesterday($dateYesterdayStart, $dateTodayStart){
        
        $em = $this->getEntityManager();
        
        $dql = 'SELECT DISTINCT c.id '
               . 'FROM \SharengoCore\Entity\Trips t '
               . 'JOIN \SharengoCore\Entity\Customers c WITH t.customer = c.id '
               . 'WHERE 1=1 '
               . 'AND t.endTx >= :dateYesterdayStart '
               . 'AND t.endTx < :dateTodayStart '
               . 'AND t.payable = :payable '
               . 'AND t.pinType IS NULL '
               . 'AND t.beginningTx > :date '
               . 'AND c.discountRate = 80 '
               . 'order by c.id';
        
        $dql = 'SELECT DISTINCT c.id '
               . 'FROM \SharengoCore\Entity\Trips t '
               . 'JOIN \SharengoCore\Entity\Customers c WITH t.customer = c.id '
               . 'WHERE c.id > 27500 '
               . 'AND t.payable = :payable '
               . 'AND t.beginningTx > :date';

        $payable = "TRUE";
        
        $query = $em->createQuery($dql);
        //$query->setParameter('dateYesterdayStart', $dateYesterdayStart);
        //$query->setParameter('dateTodayStart', $dateTodayStart);
        $query->setParameter('payable', $payable);
        $query->setParameter('date', '2015-01-01');
        
        return $query->getResult();
    }
    
    public function getCustomersRunThisMonth($dateStartLastMonth, $dateStartCurrentMonth) {
        
        $em = $this->getEntityManager();
        
        $dql = 'SELECT DISTINCT c.id '
               . 'FROM \SharengoCore\Entity\Trips t '
               . 'JOIN \SharengoCore\Entity\Customers c WITH t.customer = c.id '
               . 'WHERE 1=1 '
               . 'AND t.endTx >= :dateStartLastMonth '
               . 'AND t.endTx < :dateTodayStart '
               . 'AND t.payable = :payable '
               . 'AND t.pinType IS NULL '
               . 'AND t.beginningTx > :date '
               . 'AND c.discountRate = 80 '
               . 'order by c.id';
        
        $dql = 'SELECT DISTINCT c.id '
               . 'FROM \SharengoCore\Entity\Trips t '
               . 'JOIN \SharengoCore\Entity\Customers c WITH t.customer = c.id '
               . 'WHERE t.customer > 28800 '
               . 'AND t.payable = :payable';
        
        $payable = "TRUE";
        
        $query = $em->createQuery($dql);
        //$query->setParameter('dateStartLastMonth', $dateStartLastMonth);
        //$query->setParameter('dateStartCurrentMonth', $dateStartCurrentMonth);
        $query->setParameter('payable', $payable);
        $query->setParameter('date', '2015-01-01');
        
        return $query->getResult();
    }
    
    public function findCustomerPointsByCustomer($customerId) {
        
        $em = $this->getEntityManager();
        
        $dql='SELECT cp FROM \SharengoCore\Entity\CustomersPoints cp WHERE cp.customer = :id';
        
        $query = $em->createQuery($dql);
        $query->setParameter('id', $customerId);
        
        return $query->getResult();
    }
    
    public function checkCustomerIfAlreadyAddPointsThisMonth($customerId, $dateCurrentMonthStart, $dateNextMonthStart){
        
        $em = $this->getEntityManager();
        
        $dql = 'SELECT cp '
               . 'FROM \SharengoCore\Entity\CustomersPoints cp '
               . 'WHERE 1=1 '
               . 'AND cp.type = :type '
               . 'AND cp.customer = :customerId '
               . 'AND cp.insertTs >= :dateCurrentMonthStart '
               . 'AND cp.insertTs < :dateNextMonthStart ';
        
        $type="DRIVE";
        
        $query = $em->createQuery($dql);
        $query->setParameter('type', $type);
        $query->setParameter('customerId', $customerId);
        $query->setParameter('dateCurrentMonthStart', $dateCurrentMonthStart);
        $query->setParameter('dateNextMonthStart', $dateNextMonthStart);
         
        return $query->getResult();
        
    }
}
