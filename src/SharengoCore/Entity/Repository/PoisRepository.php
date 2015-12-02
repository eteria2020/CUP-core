<?php

namespace SharengoCore\Entity\Repository;

use Doctrine\ORM\EntityRepository;

class PoisRepository extends EntityRepository
{

    public function getTotalPois()
    {
        $em = $this->getEntityManager();
        $query = $em->createQuery('SELECT COUNT(p.id) FROM \SharengoCore\Entity\Pois p');
        return $query->getSingleScalarResult();
    }
}
