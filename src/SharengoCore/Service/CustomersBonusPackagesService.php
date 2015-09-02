<?php

namespace SharengoCore\Service;

use SharengoCore\Entity\Repository\CustomersBonusPackagesRepository as Repository;

class CountriesService
{
    /**
     * @var Repository
     */
    private $repository;

    public function __construct(Repository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param string $code
     * @return SharengoCore\Entity\CustomersBonusPackages
     */
    public function getBonusPackagesByCode($code)
    {
        return $this->repository->findOneByCode($code);
    }
}
