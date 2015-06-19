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
}