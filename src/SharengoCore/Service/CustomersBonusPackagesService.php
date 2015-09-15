<?php

namespace SharengoCore\Service;

use SharengoCore\Entity\CustomersBonusPackages;
use SharengoCore\Entity\Repository\CustomersBonusPackagesRepository as Repository;

class CustomersBonusPackagesService
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

    /**
     * @return CustomersBonusPackages[]
     */
    public function getAllBonusPackages()
    {
        return $this->repository->findAll();
    }

    public function getAvailableBonusPackges()
    {
        return $this->repository->findAvailableBonusPackages();
    }
}
