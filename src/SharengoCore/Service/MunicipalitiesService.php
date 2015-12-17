<?php

namespace SharengoCore\Service;

use SharengoCore\Entity\Queries\ActiveMunicipalities;

use Doctrine\ORM\EntityManager;

class MunicipalitiesService
{
    private $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * returns all the active municipalities, filtered by province if one is
     * provided in input
     *
     * @param string|null $province
     */
    public function activeMunicipalities($province = null)
    {
        $query = new ActiveMunicipalities($this->entityManager, $province);

        return $query();
    }
}
