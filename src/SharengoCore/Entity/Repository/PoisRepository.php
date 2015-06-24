<?php

namespace SharengoCore\Entity\Repository;

class PoisRepository extends \Doctrine\ORM\EntityRepository
{
	/*
    public function getTotalCars()
    {
        $em = $this->getEntityManager();
        $query = $em->createQuery('SELECT COUNT(c.plate) FROM \SharengoCore\Entity\Cars c');
        return $query->getSingleScalarResult();
    }
    */
}
