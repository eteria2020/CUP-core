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
               . 'WHERE t.timestampBeginning >= :dateYesterdayStart '
               . 'AND t.timestampEnd < :dateTodayStart';
        
        $dql2 = 'SELECT DISTINCT c.id '
               . 'FROM \SharengoCore\Entity\Trips t '
               . 'JOIN \SharengoCore\Entity\Customers c WITH t.customer = c.id '
               . 'WHERE c.id > 27500'
               . 'order by c.id';

        $query = $em->createQuery($dql2);
        //$query->setParameter('dateYesterdayStart', $dateYesterdayStart);
        //$query->setParameter('dateTodayStart', $dateTodayStart);
        
        return $query->getResult();
    }
}
