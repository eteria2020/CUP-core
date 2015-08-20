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
            WHERE tp.status = :status
            AND tp.invoice IS NULL
            AND tp.totalCost != 0
            ORDER BY tp.payedSuccessfullyAt ASC';

        $query = $em->createQuery($dql);

        $query->setParameter('status', 'payed_correctly');

        return $query->getResult();
    }

    public function countTotalFailedPayments()
    {
        $em = $this->getEntityManager();

        $dql = 'SELECT COUNT(tp) FROM SharengoCore\Entity\TripPayments tp '.
            'WHERE tp.status = \'wrong_payment\'';

        $query = $em->createQuery($dql);

        return $query->getSingleScalarResult();
    }

    public function findTripPaymentsForPayment()
    {
        $em = $this->getEntityManager();

        $dql = 'SELECT tp FROM SharengoCore\Entity\TripPayments tp '.
            'JOIN tp.trip t '.
            'JOIN t.customer c '.
            'WHERE tp.status = :status '.
            'AND c.paymentAble = :paymentAble';

        $query = $em->createQuery($dql);

        $query->setParameter('status', 'not_payed');
        $query->setParameter('paymentAble', true);

        return $query->getResult();
    }
}
