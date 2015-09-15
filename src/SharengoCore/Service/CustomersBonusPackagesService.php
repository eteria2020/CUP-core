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

    /**
     * @param Repository $repository
     */
    public function __construct(Repository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param integer $id
     * @return CustomersBonusPackages
     */
    public function getBonusPackageById($id)
    {
        return $this->repository->findOneById($id);
    }

    /**
     * @param string $code
     * @return CustomersBonusPackages
     */
    public function getBonusPackageByCode($code)
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

    /**
     * @return CustomersBonusPackages[]
     */
    public function getAvailableBonusPackges()
    {
        return $this->repository->findAvailableBonusPackages();
    }
}
