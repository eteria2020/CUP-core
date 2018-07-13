<?php

namespace SharengoCore\Entity\Repository;

// Externals
use Doctrine\ORM\Query\ResultSetMapping;
// Internals
use SharengoCore\Entity\Preauthorizations;

class PreauthorizationsRepository extends \Doctrine\ORM\EntityRepository
{
    /**
     * @param Preauthorizations $preauthorizations
     * @return \Cartasi\Entity\Refunds
     */
    public function findRefundbyPreauthorization(Preauthorizations $preauthorizations)
    {
        $codtrans = $preauthorizations->getTransaction()->getId().'-'.$preauthorizations->getCustomer()->getId();

        $em = $this->getEntityManager();

        $dql = "SELECT r
        FROM \Cartasi\Entity\Refunds r
        WHERE r.codtrans = :codtrans";

        $query = $em->createQuery($dql);
        $query->setParameter('codtrans', $codtrans);

        return $query->getResult();
    }
}

