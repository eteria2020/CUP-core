<?php

namespace SharengoCore\Service;

use SharengoCore\Entity\Queries\ActiveMunicipalities;

use Doctrine\ORM\EntityManager;
use SharengoCore\Entity\Repository\MunicipalityRepository;

class MunicipalitiesService
{
    private $entityManager;

    private $municipalityRepository;

    public function __construct(EntityManager $entityManager, MunicipalityRepository $municipalityRepository)
    {
        $this->entityManager = $entityManager;
        $this->municipalityRepository = $municipalityRepository;
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

    /**
     * @param $castralCode
     * @return array
     */

    public function getMunicipalityByCadastralCode($castralCode){
        return $this->municipalityRepository->findByCadastralCode($castralCode);
    }
}
