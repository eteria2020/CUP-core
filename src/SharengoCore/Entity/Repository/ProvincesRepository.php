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
            ->orderBy('p.code')
            ->getQuery();

        return $countries->getResult();
    }
}
