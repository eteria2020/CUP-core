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

    /**
     * @param Cars
     * @return bool whether the car is or not inside the zones associated to its fleet
     */
    public function checkCarInFleetZones($car)
    {
        $em = $this->getEntityManager();

        $sql = 'SELECT coalesce(bool_or(za.geo @> point(c.longitude, c.latitude)), false) AS is_in
            FROM cars c
            JOIN fleets f ON f.id = c.fleet_id
            JOIN zone_alarms_fleets zaf ON zaf.fleet_id = f.id
            JOIN zone_alarms za ON za.id = zaf.zone_alarm_id AND za.active = TRUE
            WHERE c.plate = :plate';

        $rsm = new ResultSetMapping;
        $rsm->addScalarResult('is_in', 'isIn', 'boolean');

        $query = $em->createNativeQuery($sql, $rsm);
        $query->setParameter('plate', $car->getPlate());

        return $query->getSingleScalarResult();
    }

    public function findOutOfBounds()
    {
        $em = $this->getEntityManager();

        $sql = 'SELECT c
            FROM cars c
            JOIN fleets f ON f.id = c.fleet_id
            JOIN zone_alarms_fleets zaf ON zaf.fleet_id = f.id
            JOIN zone_alarms za ON za.id = zaf.zone_alarm_id AND za.active = TRUE
            WHERE za.geo !@> point(c.longitude, c.latitude)';

        $rsm = new ResultSetMapping;
        $rsm->addEntityResult('\SharengoCore\Entity\Cars', 'c');

        $query = $em->createNativeQuery($sql, $rsm);

        return $query->getResult();
    }
}
