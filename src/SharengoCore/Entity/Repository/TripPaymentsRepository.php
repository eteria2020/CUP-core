<?php

namespace SharengoCore\Entity\Repository;

class TripPaymentsRepository extends \Doctrine\ORM\EntityRepository
{
    public function findTripPaymentsNoInvoice()
    {
        $em = $this->getEntityManager();

        $dql = 'SELECT tp
        FROM SharengoCore\Entity\TripPayments tp
        JOIN SharengoCore\Entity\Trips t
        WHERE tp.status = :status';

        $query = $em->createQuery($dql);

        $query->setParameter('status', 'not_payed');

        return $query->getResult();
    }
}
