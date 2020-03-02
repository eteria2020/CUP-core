<?php

namespace SharengoCore\Entity\Repository;

class DocumentsRepository extends \Doctrine\ORM\EntityRepository
{

    /**
     * @param $id
     * @return mixed
     */
    public function findById($id)
    {
        $em = $this->getEntityManager();
        $query = $em->createQuery('SELECT d FROM \SharengoCore\Entity\Documents d WHERE d.id = :id');
        $query->setParameter('id', $id);
        $query->setMaxResults(1);

        return $query->getOneOrNullResult();

    }

    /**
     * @param $key
     * @param $country
     * @return mixed
     */
    public function findByKeyAndCountry($key, $country = null)
    {
        $em = $this->getEntityManager();

        $sql = 'SELECT d FROM \SharengoCore\Entity\Documents d 
            WHERE 
            d.enabled = true AND 
            d.key = :key ';

        if (!is_null($country)) {
            $sql .= ' AND d.country = :country';
        }

        $query = $em->createQuery($sql);
        $query->setParameter('key', $key);

        if (!is_null($country)) {
            $query->setParameter('country', $country);
        }

        $query->setMaxResults(1);

        return $query->getOneOrNullResult();
    }

}
