<?php

namespace SharengoCore\Entity\Repository;

class ExtraPaymentTriesRepository extends \Doctrine\ORM\EntityRepository
{
    public function findExtraPaymentTry($extraPayment, $transaction)
    {
        $em = $this->getEntityManager();

        $dql = 'SELECT t
        FROM SharengoCore\Entity\ExtraPaymentTries t
        WHERE t.extraPayment = :extraPayment
        AND t.transaction = :transaction';

        $query = $em->createQuery($dql);
        $query->setParameter('extraPayment', $extraPayment);
        $query->setParameter('transaction', $transaction);
        $query->setMaxResults(1);

        return $query->getOneOrNullResult();
    }
}
