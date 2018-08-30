<?php

namespace SharengoCore\Entity\Repository;

class MaintenanceLocationsRepository extends \Doctrine\ORM\EntityRepository
{
    public function findAllActive()
    {
        $em = $this->getEntityManager();

        $dql = "SELECT a FROM \SharengoCore\Entity\MaintenanceLocations a WHERE a.enabled=TRUE";

        $query = $em->createQuery($dql);

        return $query->getResult();
    }
}
