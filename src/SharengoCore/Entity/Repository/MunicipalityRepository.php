<?php

namespace SharengoCore\Entity\Repository;

// Externals
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;

class MunicipalityRepository extends EntityRepository
{

    public function findByCadastralCode($cadastralCode)
    {
        $em = $this->getEntityManager();

        $dql = "SELECT m
        FROM \SharengoCore\Entity\Municipality m
        WHERE m.cadastralCode = :cadastral";

        $query = $em->createQuery($dql);
        $query->setParameter('cadastral', $cadastralCode);

        return $query->getResult();
    }
}
