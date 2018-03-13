<?php

namespace SharengoCore\Entity\Repository;

use SharengoCore\Entity\Webuser;


class WebusersRepository extends \Doctrine\ORM\EntityRepository
{
    /**
     * @param string $field
     * @param string $value
     * @return Customers[]
     */
    public function findById($id)
    {
        $em = $this->getEntityManager();
        $query = $em->createQuery('SELECT c FROM \SharengoCore\Entity\Webuser c WHERE id = :id');
        $query->setParameter('id', $id);

        return $query->getResult();
    }
}