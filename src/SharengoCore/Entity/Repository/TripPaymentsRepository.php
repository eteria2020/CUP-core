<?php

namespace SharengoCore\Entity\Repository;

use SharengoCore\Entity\Customers;
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

    /**
     * @param Customers $customer optional parameter to filter the results by
     *  customer
     */
    public function findTripPaymentsForPayment(Customers $customer = null, $timestampEndParam = null)
    {
        $em = $this->getEntityManager();

        $dql = 'SELECT tp FROM SharengoCore\Entity\TripPayments tp '.
            'JOIN tp.trip t '.
            'JOIN t.customer c '.
            'WHERE tp.status = :status '.
            'AND t.timestampEnd < :midnight ';

        if ($customer instanceof Customers) {
            $dql .= 'AND c = :customer ';
        }
        if ($timestampEndParam !== null){
            $dql .= 'AND t.timestampEnd >= :timestampEndParam ';
        }

        $dql .= ' ORDER BY t.timestampBeginning ASC';

        $query = $em->createQuery($dql);

        $query->setParameter('status', TripPayments::STATUS_TO_BE_PAYED);
        $query->setParameter('midnight', date_create('midnight'));

        if ($customer instanceof Customers) {
            $query->setParameter('customer', $customer);
        }

        if ($timestampEndParam !== null){
            $query->setParameter('timestampEndParam', date_create($timestampEndParam));
        }
 
        return $query->getResult();
    }

    public function findTripPaymentsWrong(Customers $customer = null, $timestampEndParam = null)
    {
        $em = $this->getEntityManager();

        $dql = 'SELECT tp FROM SharengoCore\Entity\TripPayments tp '.
            'JOIN tp.trip t '.
            'JOIN t.customer c '.
            'WHERE tp.status = :status '.
            'AND t.timestampEnd < :midnight ';

        if ($customer instanceof Customers) {
            $dql .= 'AND c = :customer ';
        }
        if ($timestampEndParam !== null){
            $dql .= 'AND t.timestampEnd >= :timestampEndParam ';
        }

        $dql .= ' ORDER BY t.timestampBeginning ASC';

        $query = $em->createQuery($dql);

        $query->setParameter('status', TripPayments::STATUS_WRONG_PAYMENT);
        $query->setParameter('midnight', date_create('midnight'));

        if ($customer instanceof Customers) {
            $query->setParameter('customer', $customer);
        }

        if ($timestampEndParam !== null){
            $query->setParameter('timestampEndParam', date_create($timestampEndParam));
        }
 
        return $query->getResult();
    }

    /**
     * 
     * @param Customers $customer
     * @param type $timestampEndParam
     * @return type
     */
        public function findTripPaymentsToBePayedAndWrong(Customers $customer = null, $timestampEndParam = null)
    {
        $em = $this->getEntityManager();

        $dql = 'SELECT tp FROM SharengoCore\Entity\TripPayments tp '.
            'JOIN tp.trip t '.
            'JOIN t.customer c '.
            'WHERE t.payable = true '.
            'AND tp.status IN (:status_to_be_payed, :status_wrong) ';

        if ($customer instanceof Customers) {
            $dql .= 'AND c = :customer ';
        }
        if ($timestampEndParam !== null){
            $dql .= 'AND t.timestampEnd >= :timestampEndParam ';
        }

        $dql .= ' ORDER BY t.timestampBeginning ASC';

        $query = $em->createQuery($dql);

        $query->setParameter('status_to_be_payed', TripPayments::STATUS_TO_BE_PAYED);
        $query->setParameter('status_wrong', TripPayments::STATUS_WRONG_PAYMENT);
        //$query->setParameter('midnight', date_create('midnight'));

        if ($customer instanceof Customers) {
            $query->setParameter('customer', $customer);
        }

        if ($timestampEndParam !== null){
            $query->setParameter('timestampEndParam', date_create($timestampEndParam));
        }
 
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
     * @param Customers $customer
     * @return TripPayments[]
     */
    public function findFailedByCustomer(Customers $customer)
    {
        $em = $this->getEntityManager();

        $dql = "SELECT tp
            FROM SharengoCore\Entity\TripPayments tp
            LEFT JOIN SharengoCore\Entity\TripPaymentTries tpt WITH tpt.tripPayment = tp
            JOIN tp.trip t
            WHERE t.customer = :customerParam
            AND tpt.outcome = 'KO'
            GROUP BY tp.id
            ORDER BY tp.id DESC";

        $query = $em->createQuery($dql);

        $query->setParameter('customerParam', $customer);

        return $query->getResult();
    }

    /**
     * @param Trips $trip
     * @return TripPayments
     */
    public function findTripPaymentForTrip(Trips $trip)
    {
        $em = $this->getEntityManager();

        $dql = 'SELECT tp
            FROM SharengoCore\Entity\TripPayments tp
            WHERE tp.trip = :trip';

        $query = $em->createQuery($dql);
        $query->setMaxResults(1);
        $query->setParameter('trip', $trip);

        return $query->getOneOrNullResult();
    }
}
