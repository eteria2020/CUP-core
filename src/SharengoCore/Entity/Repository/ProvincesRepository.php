<?php

namespace SharengoCore\Entity\Repository;

use Doctrine\Orm\EntityRepository;

/**
 * ProvincesRepository
 */
class ProvincesRepository extends EntityRepository
{
    public function getAllProvinces()
    {
        $countries = $this->createQueryBuilder('p')
            ->select('p.code, p.name')
            ->orderBy('p.name')
            ->getQuery();

        return $countries->getResult();
    }

    public function findByCode($code)
    {
        $em = $this->getEntityManager();

        $dql = "SELECT p
        FROM \SharengoCore\Entity\Provinces p
        WHERE p.code = :code";

        $query = $em->createQuery($dql);
        $query->setParameter('code', $code);

        return $query->getFirstResult();
    }

    public function findByName($name)
    {
        $em = $this->getEntityManager();

        $dql = "SELECT p
        FROM \SharengoCore\Entity\Provinces p
        WHERE p.name = :name";

        $query = $em->createQuery($dql);
        $query->setParameter('name', $name);

        return $query->getFirstResult();
    }
}
