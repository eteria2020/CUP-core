<?php

namespace SharengoCore\Entity\Repository;

// Externals
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;

/**
 * ZoneBonusRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ZoneBonusRepository extends \Doctrine\ORM\EntityRepository
{
    /**
     * @return SharengoCore\Entity\ZoneBonus[]
     */
    public function findAllActive()
    {
        $em = $this->getEntityManager();

        $dql = "SELECT z
        FROM \SharengoCore\Entity\ZoneBonus z
        WHERE z.active = true";

        $query = $em->createQuery($dql);

        return $query->getResult();
    }

    /**
     * @return SharengoCore\Entity\ZoneBonus[]
     */
    public function findAllActiveByFleet($fleet)
    {
        $em = $this->getEntityManager();

        $dql = "SELECT z
            FROM \SharengoCore\Entity\ZoneBonus z
            WHERE z.active = true
            AND :fleet MEMBER OF z.fleets";

        $query = $em->createQuery($dql);
        $query->setParameter('fleet', $fleet);

        return $query->getResult();
    }

    /**
     * @return SharengoCore\Entity\ZoneBonus[]
     */
    public function findAllActiveZonesBonusForExtraFare($bonusType=null, $fleet=null)
    {
        $em = $this->getEntityManager();

        $dql = "SELECT z
            FROM \SharengoCore\Entity\ZoneBonus z
            WHERE z.active = true
            AND z.cost NOT NULL";

        if($bonusType!==null){
            $dql .= " AND z.bonusType = :bonus_type";
        }

        if($fleet!==null){
            $dql .= " AND :fleet MEMBER OF z.fleets";
        }

        $query = $em->createQuery($dql);

        if($bonusType!==null){
            $query->setParameter('bonus_type', $bonusType);
        }

        if($fleet!==null){
            $query->setParameter('fleet', $fleet);
        }

        return $query->getResult();
    }

    /**
     * @return bool whether the point is or not inside the bonus zone
     */
    public function findBonusZonesByCoordinatesAndFleet(\SharengoCore\Entity\ZoneBonus $zoneBonus, $longitude, $latitude)
    {
        $em = $this->getEntityManager();

        $sql = "SELECT coalesce(bool_or(zb.geo @> point(:longitude, :latitude)), false) AS is_in
            FROM zone_bonus zb
            WHERE zb.active = true
            AND zb.id = :zb_id";

        $rsm = new ResultSetMapping;
        $rsm->addScalarResult('is_in', 'isIn', 'boolean');

        $query = $em->createNativeQuery($sql, $rsm);
        $query->setParameter('longitude', $longitude);
        $query->setParameter('latitude', $latitude);
        $query->setParameter('zb_id', $zoneBonus->getId());

        return $query->getSingleScalarResult();
    }
}
