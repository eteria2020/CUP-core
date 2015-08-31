<?php

namespace SharengoCore\Service;

use SharengoCore\Entity\Repository\CustomersBonusInfoRepository as Repository;

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
     * @return SharengoCore\Entity\CustomersBonusInfo
     */
    public function getBonusInfoByCode($code)
    {
        return $this->repository->findByCode($code);
    }
}
