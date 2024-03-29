<?php

namespace SharengoCore\Service;

use SharengoCore\Entity\Repository\ProvincesRepository;

class ProvincesService
{
    private $repository;

    public function __construct(ProvincesRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getAllProvinces()
    {
        $provinces = $this->repository->getAllProvinces();
        $ret = [];

        foreach ($provinces as $p) {
            $ret[$p['code']] = $p['name'];
        }

        return $ret;
    }

    public function getProvinceByCode($code) {
        return $this->repository->findByCode($code);
    }

    public function getProvinceByName($name) {
        return $this->repository->findByName($name);
    }

}
