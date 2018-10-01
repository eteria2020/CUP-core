<?php

namespace SharengoCore\Entity\Repository;

/**
 * CarsMaintenanceRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class CarsMaintenanceRepository extends \Doctrine\ORM\EntityRepository
{
    public function findLastCarsMaintenance($plate)
    {
        $em = $this->getEntityManager();
        $query = $em->createQuery("SELECT cm FROM \SharengoCore\Entity\CarsMaintenance cm WHERE cm.carPlate = :plate AND cm.endTs IS NULL ORDER BY cm.updateTs DESC");
        $query->setParameter('plate', $plate);
        $query->setMaxResults(1);

        return $query->getOneOrNullResult();
    }
    
}
