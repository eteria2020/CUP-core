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
class CustomersPointsRepository extends \Doctrine\ORM\EntityRepository {

    public function getCustomersRunYesterday($dateYesterdayStart, $dateTodayStart) {

        $em = $this->getEntityManager();

        $dql = 'SELECT DISTINCT c.id '
            . 'FROM \SharengoCore\Entity\Trips t '
            . 'JOIN \SharengoCore\Entity\Customers c WITH t.customer = c.id '
            . 'WHERE '
            . 't.endTx >= :dateYesterdayStart '
            . 'AND t.endTx < :dateTodayStart '
            . 'AND t.payable = :payable '
            . 'AND t.pinType IS NULL '
            . 'AND t.beginningTx > :date '
            . 'ORDER BY c.id';
        
        $payable = "TRUE";

        $query = $em->createQuery($dql);
        $query->setParameter('dateYesterdayStart', $dateYesterdayStart);
        $query->setParameter('dateTodayStart', $dateTodayStart);
        $query->setParameter('payable', $payable);
        $query->setParameter('date', '2015-01-01');

        return $query->getResult();
    }//end getCustomersRunYesterday

    public function getCustomersRunThisMonth($dateTodayStart, $dateStartCurrentMonth) {

        $em = $this->getEntityManager();

        $dql = 'SELECT DISTINCT c.id '
            . 'FROM \SharengoCore\Entity\Trips t '
            . 'JOIN \SharengoCore\Entity\Customers c WITH t.customer = c.id '
            . 'WHERE '
            . 't.endTx >= :dateStartCurrentMonth '
            . 'AND t.endTx < :dateTodayStart '
            . 'AND t.payable = :payable '
            . 'AND t.pinType IS NULL '
            . 'AND t.beginningTx > :date '
            . 'ORDER BY c.id';

        $payable = "TRUE";

        $query = $em->createQuery($dql);
        $query->setParameter('dateStartCurrentMonth', $dateStartCurrentMonth);
        $query->setParameter('dateTodayStart', $dateTodayStart);
        $query->setParameter('payable', $payable);
        $query->setParameter('date', '2015-01-01');

        return $query->getResult();
    }
    
    public function getCustomerPointsCheckCluster($customerId) {
        $em = $this->getEntityManager();

        $dql = 'SELECT cp FROM \SharengoCore\Entity\CustomersPoints cp WHERE cp.customer = :id '
                . 'AND cp.type = :typeCluster';

        $query = $em->createQuery($dql);
        $query->setParameter('id', $customerId);
        $query->setParameter('typeCluster', "CLUSTER");

        return $query->getResult();
    }

    public function findCustomerPointsByCustomer($customerId) {

        $em = $this->getEntityManager();

        $dql = 'SELECT cp FROM \SharengoCore\Entity\CustomersPoints cp WHERE cp.customer = :id';

        $query = $em->createQuery($dql);
        $query->setParameter('id', $customerId);

        return $query->getResult();
    }

    public function checkCustomerIfAlreadyAddPointsThisMonth($customerId, $dateCurrentMonthStart, $dateNextMonthStart) {

        $em = $this->getEntityManager();

        $dql = 'SELECT cp '
            . 'FROM \SharengoCore\Entity\CustomersPoints cp '
            . 'WHERE '
            . 'cp.type = :type '
            . 'AND cp.customer = :customerId '
            . 'AND cp.insertTs >= :dateCurrentMonthStart '
            . 'AND cp.insertTs < :dateNextMonthStart ';

        $type = "DRIVE";

        $query = $em->createQuery($dql);
        $query->setParameter('type', $type);
        $query->setParameter('customerId', $customerId);
        $query->setParameter('dateCurrentMonthStart', $dateCurrentMonthStart);
        $query->setParameter('dateNextMonthStart', $dateNextMonthStart);

        return $query->getResult();
    }
    
    public function getTripsByCustomerForAddPointClusterLastMonth($customerId, $dateTodayStart, $dateStartCurrentMonth) {

        $em = $this->getEntityManager();

        $dql = 'SELECT cp '
                . 'FROM \SharengoCore\Entity\CustomersPoints cp '
                . 'WHERE 1=1 '
                . 'AND cp.insertTs >= :dateStartCurrentMonth '
                . 'AND cp.insertTs < :dateTodayStart '
                . 'AND cp.customer = :customerId '
                . 'AND cp.type = :typeDrive '
                ;


        $query = $em->createQuery($dql);
        $query->setParameter('dateTodayStart', $dateTodayStart);
        $query->setParameter('dateStartCurrentMonth', $dateStartCurrentMonth);
        $query->setParameter('customerId', $customerId);
        $query->setParameter('typeDrive', "DRIVE");

        return $query->getResult();
    }
    
    public function getTripsByCustomerForAddPointClusterTwotMonthAgo($customerId, $dateStartLastMonth, $dateStartCurrentMonth) {

        $em = $this->getEntityManager();

        $dql = 'SELECT cp '
                . 'FROM \SharengoCore\Entity\CustomersPoints cp '
                . 'WHERE 1=1 '
                . 'AND cp.insertTs >= :dateStartLastMonth '
                . 'AND cp.insertTs < :dateStartCurrentMonth '
                . 'AND cp.customer = :customerId '
                . 'AND cp.type = :typeDrive '
                ;

        $payable = "TRUE";

        $query = $em->createQuery($dql);
        $query->setParameter('dateStartLastMonth', $dateStartLastMonth);
        $query->setParameter('dateStartCurrentMonth', $dateStartCurrentMonth);
        $query->setParameter('customerId', $customerId);
        $query->setParameter('typeDrive', "DRIVE");


        return $query->getResult();
    }
    
    public function getAllCustomerInCustomersPoints($dateStart, $dateEnd) {
       
        $em = $this->getEntityManager();

        $dql = 'SELECT cp '
                . 'FROM \SharengoCore\Entity\CustomersPoints cp '
                . 'WHERE 1=1 '
                . 'AND cp.insertTs >= :dateStart '
                . 'AND cp.insertTs < :dateEnd '
                . 'AND cp.type = :typeDrive '
                . 'ORDER BY cp.id '
                ;

        $query = $em->createQuery($dql);
        $query->setParameter('dateStart', $dateStart);
        $query->setParameter('dateEnd', $dateEnd);
        $query->setParameter('typeDrive', "DRIVE");


        return $query->getResult();
        
    }
    
    public function getAllCustomerRunInMonth($dateStart, $dateEnd) {
       
        $em = $this->getEntityManager();

        $dql = 'SELECT DISTINCT c.id '
            . 'FROM \SharengoCore\Entity\Trips t '
            . 'JOIN \SharengoCore\Entity\Customers c WITH t.customer = c.id '
            . 'WHERE '
            . 't.endTx >= :dateStart '
            . 'AND t.endTx < :dateEnd '
            . 'AND t.payable = :payable '
            . 'AND t.pinType IS NULL '
            . 'AND t.timestampBeginning > :date1 '
            . 'AND t.timestampEnd < :date2 '
            //. 'AND (t.timestampEnd - t.timestampBeginning)  '
            . 'ORDER BY c.id';
        
        $payable = "TRUE";

        $query = $em->createQuery($dql);
        $query->setParameter('dateStart', $dateStart);
        $query->setParameter('dateEnd', $dateEnd);
        $query->setParameter('payable', $payable);
        $query->setParameter('date1', '2017-09-18');
        $query->setParameter('date2', '2018-01-01');

        return $query->getResult();
        
    }

    public function deleteCustomersPoints() {
        
        
        $em = $this->getEntityManager();

        $dql = 'DELETE '
                . 'FROM \SharengoCore\Entity\CustomersPoints cp '
                ;

        $query = $em->createQuery($dql);

        return $query->getResult();
        
    }

}
