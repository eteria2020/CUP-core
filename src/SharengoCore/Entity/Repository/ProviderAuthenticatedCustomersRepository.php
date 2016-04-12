<?php

namespace SharengoCore\Entity\Repository;

use Doctrine\ORM\EntityManager;

class ProviderAuthenticatedCustomersRepository
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function findById($id)
    {
        $dql = 'SELECT pac FROM \SharengoCore\Entity\ProviderAuthenticatedCustomer pac ' .
            'WHERE pac.id = :id';

        $query = $this->entityManager->createQuery($dql);
        $query->setParameter('id', $id);

        return $query->getOneOrNullResult();
    }
}
