<?php

namespace SharengoCore\Entity\Repository;

use Doctrine\ORM\Query\ResultSetMapping;

/**
 * CarsRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class CarsRepository extends \Doctrine\ORM\EntityRepository
{
    public function getTotalCars()
    {
        $em = $this->getEntityManager();
        $query = $em->createQuery('SELECT COUNT(c.plate) FROM \SharengoCore\Entity\Cars c');
        return $query->getSingleScalarResult();
    }

    public function findCarsEligibleForAlarmCheck()
    {
        $em = $this->getEntityManager();

        $dql = "SELECT c
        FROM \SharengoCore\Entity\Cars c
        WHERE NOT EXISTS
        (SELECT 1
        FROM \SharengoCore\Entity\Trips t
        WHERE t.car = c
        AND t.timestampEnd is null)";

        $query = $em->createQuery($dql);

        return $query->getResult();
    }

    public function findPublicCars()
    {
        $em = $this->getEntityManager();

        $dql = "SELECT c
        FROM \SharengoCore\Entity\Cars c
        WHERE c.status = 'operative'
        AND c.active = true
        AND c.hidden = false
        AND c.longitude != 0
        AND c.latitude != 0";

        $query = $em->createQuery($dql);

        return $query->getResult();
    }

    public function getCarIfInAlarmZone($car, $zone)
    {
        $em = $this->getEntityManager();

        $sql = "SELECT c.plate
            FROM cars c, zone_alarms z
            WHERE c.plate = ?
            AND z.id = ?
            AND z.active = true
            AND z.geo @> point(c.longitude, c.latitude)";

        $rsm = new ResultSetMapping;
        $rsm->addEntityResult('SharengoCore\Entity\Cars', 'c');
        $rsm->addFieldResult('c', 'plate', 'plate');

        $query = $em->createNativeQuery($sql, $rsm);
        $query->setParameter(1, $car->getPlate());
        $query->setParameter(2, $zone->getId());

        return $query->getOneOrNullResult();
    }

}
