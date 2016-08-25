<?php

namespace SharengoCore\Entity\Repository;

class FreeFaresRepository extends \Doctrine\ORM\EntityRepository
{
    /**
     * @return SharengoCore\Entity\FreeFares[]
     */
    public function findAllActive()
    {
        $em = $this->getEntityManager();

        $dql = "SELECT f
        FROM \SharengoCore\Entity\FreeFares f
        WHERE f.active = true";

        $query = $em->createQuery($dql);

        return $query->getResult();
    }
}
