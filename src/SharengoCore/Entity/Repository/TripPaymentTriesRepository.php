<?php

namespace SharengoCore\Entity\Repository;

class TripPaymentsRepository extends \Doctrine\ORM\EntityRepository
{
    public function findTripPaymentTry($tripPayment, $transaction)
    {
        $em = $this->getEntityManager();

        $dql = 'SELECT t
        FROM SharengoCore\Entity\TripPaymentTries t
        WHERE t.tripPayment = :tripPayment
        AND t.transaction = :transaction';

        $query = $em->createQuery($dql);
        $query->setParameter('tripPayment', $tripPayment);
        $query->setParameter('transaction', $transaction);
        $query->setMaxResults(1);

        return $query->getOneOrNullResult();
    }
}
