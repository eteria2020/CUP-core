<?php

namespace SharengoCore\Entity\Repository;

/**
 * CustomersNoteRepository
 */
class CustomersNoteRepository extends \Doctrine\ORM\EntityRepository
{
    public function findByCustomer($customer)
    {
        $em = $this->getEntityManager();
        $dql = 'SELECT cn
            FROM \SharengoCore\Entity\CustomersNote cn
            WHERE cn.customer = :customer
            ORDER BY cn.insertedTs DESC';

        $query = $em->createQuery($dql);
        $query->setParameter('customer', $customer);

        return $query->getResult();
    }
}
