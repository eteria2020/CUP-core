<?php

namespace SharengoCore\Entity\Repository;

class MaintenanceLocationsRepository extends \Doctrine\ORM\EntityRepository
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
}
