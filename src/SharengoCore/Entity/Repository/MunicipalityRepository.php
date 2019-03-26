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

    public function findByProvinceTown($province, $town)
    {
        $em = $this->getEntityManager();

        $dql = "SELECT m
        FROM \SharengoCore\Entity\Municipality m
        WHERE 
        m.active = true AND 
        m.province = :province AND
        m.name = :town";

        $query = $em->createQuery($dql);
        $query->setParameter('province', strtoupper(trim($province)));
        $query->setParameter('town', strtoupper(trim($town)));

        return $query->getResult();
    }
}
