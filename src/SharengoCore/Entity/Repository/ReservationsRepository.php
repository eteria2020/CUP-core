<?php

namespace SharengoCore\Entity\Repository;
use SharengoCore\Entity\Trips;

/**
 * Class ReservationsRepository
 * @package SharengoCore\Entity\Repository
 */
class ReservationsRepository extends \Doctrine\ORM\EntityRepository
{
    /**
     * @return integer
     */
    public function getTotalReservations()
    {
        $em = $this->getEntityManager();
        $query = $em->createQuery('SELECT COUNT(r.id) FROM \SharengoCore\Entity\Reservations r');
        return $query->getSingleScalarResult();
    }

    /**
     * @param string $plate
     * @return Reservations[]
     */
    public function findActiveReservationsByCar($plate)
    {
        $em = $this->getEntityManager();
        $query = $em->createQuery("SELECT t FROM \SharengoCore\Entity\Reservations t WHERE t.car = :id AND t.active = :active");
        $query->setParameter('id', $plate);
        $query->setParameter('active', true);

        return $query->getResult();
    }
    
    /**
     * @param string $plate
     * @return Reservations[]
     */
    
    public function findReservations4h($customer, $car)
    {   
        $time = date_create(date("Y-m-d H:i:s"));
        $em = $this->getEntityManager();
        //SELECT car_plate FROM reservations WHERE car_plate=$2 AND customer_id=$1 AND ts >= (now() - interval '4' hour)) 
        $query = $em->createQuery("SELECT t FROM \SharengoCore\Entity\Reservations t WHERE t.customer = :id AND t.car = :car AND t.ts >= :time");
        $query->setParameter('id', $customer);
        $query->setParameter('car', $car);
        $query->setParameter('time',  date_sub($time, date_interval_create_from_date_string('4 hours')));
        return $query->getResult();
    }
    
        /**
     * @param string $plate
     * @return Reservations[]
     */
    
    public function findReservationsArchive4h($customer, $car)
    {   
        $time = date_create(date("Y-m-d H:i:s"));
        $em = $this->getEntityManager();
        //SELECT car_plate FROM reservations_archive WHERE car_plate=$2 AND customer_id=$1 AND ts >= (now() - interval '4' hour)
        $query = $em->createQuery("SELECT t FROM \SharengoCore\Entity\ReservationsArchive t WHERE t.customer = :id AND t.car = :car AND t.ts >= :time");
        $query->setParameter('id', $customer);
        $query->setParameter('car', $car);
        $query->setParameter('time',  date_sub($time, date_interval_create_from_date_string('4 hours')));

        return $query->getResult();
    }

    public function findReservationByTrip(Trips $trip)
    {
        $em = $this->getEntityManager();
        $query = $em->createQuery("SELECT t FROM \SharengoCore\Entity\ReservationsArchive t WHERE t.customer = :customer AND t.car = :car AND DATE_ADD(t.consumedTs, 60, 'SECOND') >= :time AND t.consumedTs <= :time AND t.reason = 'USED'");
        $query->setParameter('customer', $trip->getCustomer());
        $query->setParameter('car', $trip->getCar());
        $query->setParameter('time', $trip->getTimestampBeginning());
        $query->setMaxResults(1);
        return $query->getOneOrNullResult();
    }
    
    /**
     * @param Customers $customer
     * @return Reservations[]
     */
    public function findActiveReservationsByCustomer($customer)
    {
        $em = $this->getEntityManager();
        $query = $em->createQuery("SELECT t FROM \SharengoCore\Entity\Reservations t WHERE t.customer = :id AND t.active = :active");
        $query->setParameter('id', $customer);
        $query->setParameter('active', true);

        return $query->getResult();
    }

    /**
     * @return Reservations[]
     */
    public function findReservationsToDelete()
    {
        $em = $this->getEntityManager();

        $dql = "SELECT re
                FROM \SharengoCore\Entity\Reservations re
                WHERE re.toSend = false
                AND (
                    re.consumedTs IS NOT NULL
                    OR (re.length != -1 AND DATE_ADD(re.beginningTs, re.length, 'SECOND') < CURRENT_TIMESTAMP())
                    OR re.deletedTs IS NOT NULL
                    OR re.active = false
                )";

        $query = $em->createQuery($dql);

        return $query->getResult();
    }
}
