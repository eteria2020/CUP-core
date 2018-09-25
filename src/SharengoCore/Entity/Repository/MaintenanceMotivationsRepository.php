<?php

namespace SharengoCore\Entity\Repository;

class MaintenanceMotivationsRepository extends \Doctrine\ORM\EntityRepository
{
    /**
     * @return SharengoCore\Entity\MaintenanceMotivations
     */
    public function findAllActive()
    {
        $em = $this->getEntityManager();

        $dql = "SELECT a FROM \SharengoCore\Entity\MaintenanceMotivations a WHERE a.enabled=TRUE";

        $query = $em->createQuery($dql);

        return $query->getResult();
    }
    
    public function findAll()
    {
        $em = $this->getEntityManager();

        $dql = "SELECT a FROM \SharengoCore\Entity\MaintenanceMotivations a ORDER BY a.id ASC";

        $query = $em->createQuery($dql);

        return $query->getResult();
    }
    
    public function findAllNotActive()
    {
        $em = $this->getEntityManager();

        $dql = "SELECT a.id FROM \SharengoCore\Entity\MaintenanceMotivations a WHERE a.enabled=FALSE ORDER BY a.id ASC";

        $query = $em->createQuery($dql);

        return $query->getResult();
    }
}
