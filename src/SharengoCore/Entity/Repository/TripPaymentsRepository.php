<?php

namespace SharengoCore\Entity\Repository;

use SharengoCore\Entity\Trips;
use SharengoCore\Entity\TripPayments;

class TripPaymentsRepository extends \Doctrine\ORM\EntityRepository
{
    public function findTripPaymentsNoInvoice()
    {
        $em = $this->getEntityManager();

        $dql = 'SELECT tp
            FROM SharengoCore\Entity\TripPayments tp
            JOIN tp.trip t
            WHERE tp.status = :status
            AND tp.invoice IS NULL
            AND tp.totalCost != 0
            ORDER BY tp.payedSuccessfullyAt ASC';

        $query = $em->createQuery($dql);

        $query->setParameter('status', TripPayments::STATUS_PAYED_CORRECTLY);

        return $query->getResult();
    }

    public function countTotalFailedPayments()
    {
        $em = $this->getEntityManager();

        $dql = 'SELECT COUNT(tp) FROM SharengoCore\Entity\TripPayments tp '.
            'WHERE tp.status = :status';

        $query = $em->createQuery($dql);

        $query->setParameter('status', TripPayments::STATUS_WRONG_PAYMENT);

        return $query->getSingleScalarResult();
    }

    public function findTripPaymentsForPayment()
    {
        $em = $this->getEntityManager();

        $dql = 'SELECT tp FROM SharengoCore\Entity\TripPayments tp '.
            'JOIN tp.trip t '.
            'JOIN t.customer c '.
            'WHERE tp.status = :status '.
            'AND c.paymentAble = :paymentAble '.
            'AND t.timestampEnd < :midnight';

        $query = $em->createQuery($dql);

        $query->setParameter('status', TripPayments::STATUS_TO_BE_PAYED);
        $query->setParameter('paymentAble', true);
        $query->setParameter('midnight', date_create('midnight'));

        return $query->getResult();
    }

    public function findTripPaymentsForUserPayment($customer)
    {
        $em = $this->getEntityManager();

        $dql = 'SELECT tp FROM SharengoCore\Entity\TripPayments tp '.
            'JOIN tp.trip t '.
            'WHERE tp.status = :status '.
            'AND t.customer = :customer';

        $query = $em->createQuery($dql);

        $query->setParameter('status', TripPayments::STATUS_TO_BE_PAYED);
        $query->setParameter('customer', $customer);

        return $query->getResult();
    }

    public function findFirstTripPaymentNotPayedByCustomer($customer)
    {
        $em = $this->getEntityManager();

        $dql = 'SELECT tp
            FROM SharengoCore\Entity\TripPayments tp
            JOIN tp.trip t
            WHERE t.customer = :customer
            AND (tp.status = :to_be_payed
            OR tp.status = :wrong_payment)
            ORDER BY t.timestampBeginning ASC';

        $query = $em->createQuery($dql);
        $query->setParameter('customer', $customer);
        $query->setParameter('to_be_payed', TripPayments::STATUS_TO_BE_PAYED);
        $query->setParameter('wrong_payment', TripPayments::STATUS_WRONG_PAYMENT);
        $query->setMaxResults(1);

        return $query->getOneOrNullResult();
    }

    /**
     * @param Trips $trip
     */
    public function deleteTripPaymentsByTrip(Trips $trip)
    {
        $dql = "DELETE FROM \SharengoCore\Entity\TripPayments tb ".
            "WHERE tb.trip = :trip";

        $query = $this->getEntityManager()->createQuery($dql);
        $query->setParameter('trip', $trip);

        return $query->execute();
    }
}
