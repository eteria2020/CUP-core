<?php

namespace SharengoCore\Entity\Repository;

class MaintenanceLocationsRepository extends \Doctrine\ORM\EntityRepository
{
    public function findAllActive()
    {
        $em = $this->getEntityManager();

        $dql = "SELECT a FROM \SharengoCore\Entity\MaintenanceLocations a WHERE a.enabled=TRUE ORDER BY a.id ASC";

        $query = $em->createQuery($dql);

        return $query->getResult();
    }
    
    public function findAll()
    {
        $em = $this->getEntityManager();

        $dql = "SELECT a FROM \SharengoCore\Entity\MaintenanceLocations a ORDER BY a.id ASC";

        $query = $em->createQuery($dql);

        return $query->getResult();
    }
    
    public function findAllNotActive()
    {
        $em = $this->getEntityManager();

        $dql = "SELECT a.id FROM \SharengoCore\Entity\MaintenanceLocations a WHERE a.enabled=FALSE ORDER BY a.id ASC";

        $query = $em->createQuery($dql);

        return $query->getResult();
    }
}
