<?php

namespace SharengoCore\Entity\Repository;

/**
 * TripsRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class TripsRepository extends \Doctrine\ORM\EntityRepository
{
    public function findTripsByCustomer($customerId)
    {
        $em = $this->getEntityManager();
        $query = $em->createQuery("SELECT t FROM \SharengoCore\Entity\Trips t WHERE t.customer = :id");
        $query->setParameter('id', $customerId);

        return $query->getResult();
    }
}