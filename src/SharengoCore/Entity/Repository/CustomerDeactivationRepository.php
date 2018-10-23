<?php

namespace SharengoCore\Entity\Repository;

// Externals
use Doctrine\ORM\Query\ResultSetMapping;
// Internals
use SharengoCore\Entity\CustomerDeactivation;

class CustomerDeactivationRepository extends \Doctrine\ORM\EntityRepository
{
    public function findbyIdOrderByInsertedTs($customer)
    {
        $em = $this->getEntityManager();

        $dql = "SELECT cd
        FROM \SharengoCore\Entity\CustomerDeactivation cd
        WHERE cd.customer = :customer
        ORDER BY cd.insertedTs DESC";

        $query = $em->createQuery($dql);
        $query->setParameter('customer', $customer);

        $query->setMaxResults(1);

        return $query->getResult();
    }
}
