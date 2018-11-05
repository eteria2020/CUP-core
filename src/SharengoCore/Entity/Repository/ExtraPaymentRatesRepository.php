<?php

namespace SharengoCore\Entity\Repository;

use SharengoCore\Entity\Customers;
use SharengoCore\Entity\TripPayments;
use SharengoCore\Entity\ExtraPayments;

use Doctrine\ORM\Query\ResultSetMapping;

class ExtraPaymentRatesRepository extends \Doctrine\ORM\EntityRepository
{

    public function findByExtraPaymentFather($extraPaymentId) {
        $em = $this->getEntityManager();

        $dql = 'SELECT e FROM SharengoCore\Entity\ExtraPaymentRates e '.
            'WHERE e.extraPaymentFather = :id '.
            'ORDER BY e.id ASC';

        $query = $em->createQuery($dql);

        $query->setParameter('id', $extraPaymentId);

        return $query->getResult();
    }
    
    public function ratesPaidByExtraPaymentFather($extraPaymentId) {
        $em = $this->getEntityManager();

        $dql = 'SELECT SUM(e.amount) FROM SharengoCore\Entity\ExtraPaymentRates e '.
            'JOIN SharengoCore\Entity\ExtraPayments ep WITH ep.id = e.extraPayment '.
            'WHERE e.extraPaymentFather = :id '.
            'AND ep.status = :status';

        $query = $em->createQuery($dql);

        $query->setParameter('id', $extraPaymentId);
        $query->setParameter('status', ExtraPayments::STATUS_PAYED_CORRECTLY);

        return $query->getResult();
    }
    
    public function getAllRateToBeCharged($date) {
        $em = $this->getEntityManager();

        $dql = 'SELECT e FROM SharengoCore\Entity\ExtraPaymentRates e '.
            'WHERE e.debitTs <= :date '.
            'AND e.extraPayment IS NULL';

        $query = $em->createQuery($dql);

        $query->setParameter('date', $date);

        return $query->getResult();
    }
    
    public function getExtraPaymentFather($extraPaymentId) {
        $em = $this->getEntityManager();

        $dql = 'SELECT ep.id FROM SharengoCore\Entity\ExtraPaymentRates e '.
            'JOIN SharengoCore\Entity\ExtraPayments ep WITH ep.id = e.extraPaymentFather '.
            'WHERE e.extraPayment = :id ';

        $query = $em->createQuery($dql);

        $query->setParameter('id', $extraPaymentId);

        return $query->getResult();
    }
    
    
    

}
