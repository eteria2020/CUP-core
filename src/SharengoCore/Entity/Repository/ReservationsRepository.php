<?php

namespace SharengoCore\Entity\Repository;

/**
 * Class ReservationsRepository
 * @package SharengoCore\Entity\Repository
 */
class ReservationsRepository extends \Doctrine\ORM\EntityRepository
{
    public function getTotalReservations()
    {
        $em = $this->getEntityManager();
        $query = $em->createQuery('SELECT COUNT(r.id) FROM \SharengoCore\Entity\Reservations r');
        return $query->getSingleScalarResult();
    }

    public function findActiveReservationsByCar($plate)
    {
        $em = $this->getEntityManager();
        $query = $em->createQuery("SELECT t FROM \SharengoCore\Entity\Reservations t WHERE t.car = :id AND t.active = :active");
        $query->setParameter('id', $plate);
        $query->setParameter('active', true);

        return $query->getResult();
    }

    public function findActiveReservationsByCustomer($customer)
    {
        $em = $this->getEntityManager();
        $query = $em->createQuery("SELECT t FROM \SharengoCore\Entity\Reservations t WHERE t.customer = :id AND t.active = :active");
        $query->setParameter('id', $customer);
        $query->setParameter('active', true);

        return $query->getResult();
    }

}