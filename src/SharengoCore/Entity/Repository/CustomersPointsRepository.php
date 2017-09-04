<?php

namespace SharengoCore\Entity\Repository;

use SharengoCore\Entity\Customers;
use SharengoCore\Entity\PromoCodes;
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
               //. 't.timestampBeginning >= :dateYesterdayStart '
               . 'AND t.timestampEnd < :dateTodayStart'
               . 'AND t.payable = :payable';
        
        $dql2 = 'SELECT DISTINCT c.id '
               . 'FROM \SharengoCore\Entity\Trips t '
               . 'JOIN \SharengoCore\Entity\Customers c WITH t.customer = c.id '
               . 'WHERE c.id > 27500'
               . 'order by c.id';

        $payable = "TRUE";
        
        $query = $em->createQuery($dql2);
        //$query->setParameter('dateYesterdayStart', $dateYesterdayStart);
        //$query->setParameter('dateTodayStart', $dateTodayStart);
        //$query->setParameter('payable', $payable);
        
        return $query->getResult();
    }
    
    public function getCustomersRunThisMonth($dateStartLastMonth, $dateStartCurrentMonth) {
        
        $em = $this->getEntityManager();
        
        $dql = 'SELECT DISTINCT c.id '
               . 'FROM \SharengoCore\Entity\Trips t '
               . 'JOIN \SharengoCore\Entity\Customers c WITH t.customer = c.id '
               . 'WHERE t.timestampBeginning >= :dateStartLastMonth '
               . 'AND t.timestampEnd < :dateTodayStart'
               . 'AND t.payable = :payable';
        
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
        
        return $query->getResult();
    }
    
    public function findCustomerPointsByCustomer($customerId) {
        
        $em = $this->getEntityManager();
        
        $dql='SELECT cp FROM \SharengoCore\Entity\CustomersPoints cp WHERE cp.customer = :id';
        
        $query = $em->createQuery($dql);
        $query->setParameter('id', $customerId);
        
        return $query->getResult();
    }
}
