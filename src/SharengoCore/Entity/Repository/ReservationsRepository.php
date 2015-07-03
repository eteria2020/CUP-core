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

        $dql = "SELECT re, ca, cu
        FROM \SharengoCore\Entity\Reservations re
        JOIN re.car ca
        JOIN re.customer cu
        WHERE re.consumedTs != :consumedTsVal
        OR (re.length != :lengthVal AND DATE_ADD(re.beginningTs, (re.length / 86400), 'DAY') < :nowVal)
        OR (re.active = :activeVal AND re.toSend = :toSendVal)";

        $query = $em->createQuery($dql);

        $query->setParameter('consumedTsVal', null);
        $query->setParameter('lengthVal', -1);
        $query->setParameter('nowVal', date_create());
        $query->setParameter('activeVal', false);
        $query->setParameter('toSendVal', false);

        return $query->getResult();
    }

}
