<?php

namespace SharengoCore\Entity\Repository;

use SharengoCore\Entity\Trips;
use SharengoCore\Entity\TripPayments;

use Doctrine\ORM\Query\ResultSetMapping;

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
            ORDER BY t.timestampBeginning ASC';

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

    /**
     * @param \DateTime $start
     * @param \DateTime $end
     * @param string $dateGroupingFormat a valid postgresql format for dates
     *     used to specify the grouping of the results
     *     Example: to group by day use 'YYYY-MM-DD'
     *         to group by week use 'YYYY-MM-W'
     *         to group by month use 'YYYY-MM'
     * @return array[]
     */
    public function findPayedBetween(\DateTime $start, \DateTime $end, $dateGroupingFormat = 'YYYY-MM-DD')
    {
        $em = $this->getEntityManager();

        $sql = 'SELECT to_char(tp.payed_successfully_at, :groupingFormat) AS tp_date,
                f.name AS f_name,
                sum(tp.total_cost) AS tp_amount
            FROM trip_payments tp
            LEFT JOIN trips t ON t.id = tp.trip_id
            LEFT JOIN fleets f ON t.fleet_id = f.id
            WHERE tp.payed_successfully_at >= :start
            AND tp.payed_successfully_at < :end
            GROUP BY 1, 2
            ORDER BY 1, 2';

        $rsm = new ResultSetMapping;
        $rsm->addScalarResult('tp_date', 'tp_date', 'string');
        //$rsm->addEntityResult('\SharengoCore\Entity\Fleet', 'f');
        //$rsm->addFieldResult('f', 'id', 'id');
        $rsm->addScalarResult('f_name', 'f_name', 'string');
        $rsm->addScalarResult('tp_amount', 'tp_amount', 'integer');

        $query = $em->createNativeQuery($sql, $rsm);
        $query->setParameter('groupingFormat', $dateGroupingFormat);
        $query->setParameter('start', $start->format('Y-m-d H:i:s'));
        $query->setParameter('end', $end->format('Y-m-d H:i:s'));

        return $query->getResult();
    }
}
