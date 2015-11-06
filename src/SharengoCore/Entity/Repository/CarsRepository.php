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

        $sql = "SELECT coalesce(bool_or(za.geo @> point(c.longitude, c.latitude)), false) AS is_in
            FROM cars c
            JOIN fleets f ON f.id = c.fleet_id
            JOIN zone_alarms_fleets zaf ON zaf.fleet_id = f.id
            JOIN zone_alarms za ON za.id = zaf.zone_alarm_id AND za.active = TRUE
            WHERE c.plate = :plate";

        $rsm = new ResultSetMapping;
        $rsm->addScalarResult('is_in', 'isIn', 'boolean');

        $query = $em->createNativeQuery($sql, $rsm);
        $query->setParameter('plate', $car->getPlate());

        return $query->getSingleScalarResult();
    }

    public function findReserved()
    {
        $em = $this->getEntityManager();

        $dql = "SELECT c.plate
            FROM \SharengoCore\Entity\Cars c
            JOIN \SharengoCore\Entity\Reservations r WITH r.car = c AND r.active = true
            WHERE c.hidden = false
            ORDER BY c.plate ASC";

        $query = $em->createQuery($dql);

        return $query->getResult();
    }

    public function findBusy()
    {
        $em = $this->getEntityManager();

        $dql = "SELECT c.plate
            FROM \SharengoCore\Entity\Cars c
            JOIN \SharengoCore\Entity\Trips t WITH t.car = c AND t.timestampEnd IS NOT NULL
            WHERE c.hidden = false
            ORDER BY c.plate ASC";

        $query = $em->createQuery($dql);

        return $query->getResult();
    }

    /**
     * Returns an array of key => value pairs where the key is the plate of the
     * car and the value is the amount of minutes since the last trip it has made.
     *
     * The outer query aggregates the results of the subquery generating json.
     * The subquery selects the plate of the car and the highest timestamp of
     * the relative trips. It then subtracts that timestamp from the current
     * time and extracts the seconds from the result. It then converts the
     * result to minutes and rounds it up.
     */
    public function findSinceLastTrip()
    {
        $em = $this->getEntityManager();

        $sql = "SELECT json_object_agg(plate, ts_end) as value
            FROM (
                SELECT
                    c.plate as plate,
                    round(extract('epoch' from (now() - MAX(t.timestamp_end))) / 60) as ts_end
                FROM trips t
                LEFT JOIN cars c ON t.car_plate = c.plate
                WHERE t.timestamp_end IS NOT NULL
                AND c.hidden = false
                GROUP BY c.plate
                ORDER BY c.plate ASC
            ) sub_q";

        $rsm = new ResultSetMapping;
        $rsm->addScalarResult('value', 'value', 'json_array');

        $query = $em->createNativeQuery($sql, $rsm);

        return $query->getResult();
    }

    public function findOutOfBounds()
    {
        $em = $this->getEntityManager();

        $sql = "SELECT c.plate
            FROM cars c
            JOIN fleets f ON f.id = c.fleet_id
            JOIN zone_alarms_fleets zaf ON zaf.fleet_id = f.id
            JOIN zone_alarms za ON za.id = zaf.zone_alarm_id AND za.active = TRUE
            WHERE NOT (za.geo @> point(c.longitude, c.latitude))
            AND c.hidden = false
            ORDER BY c.plate ASC";

        $rsm = new ResultSetMapping;
        $rsm->addEntityResult('\SharengoCore\Entity\Cars', 'c');

        $query = $em->createNativeQuery($sql, $rsm);

        return $query->getResult();
    }
}
