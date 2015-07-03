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

    public function findReservationsToDelete()
    {
        $em = $this->getEntityManager();

        $dql = "SELECT r
        FROM \SharengoCore\Entity\Reservations r
        WHERE r.consumedTs != :consumedTsVal
        OR (r.length != :lengthVal AND DATE_ADD(r.beginningTs, (r.length / 60 / 60 / 24), 'DAY') < :nowVal)
        OR (r.active = :activeVal AND r.toSend = :toSendVal)";

        $query = $em->createQuery($dql);

        $query->setParameter('consumedTsVal', null);
        $query->setParameter('lengthVal', -1);
        $query->setParameter('nowVal', date_create());
        $query->setParameter('activeVal', false);
        $query->setParameter('toSendVal', false);

        return $query->getResult();
    }

}
