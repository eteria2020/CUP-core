<?php

namespace SharengoCore\Service;

use SharengoCore\Entity\Repository\CountriesRepository;

class CountriesService
{
    private $repository;

    public function __construct(CountriesRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getAllCountries()
    {
        $countries = $this->repository->getAllCountries();
        $ret = [];

        foreach ($countries as $c) {
            $ret[$c['code']] = $c['name'];
        }

        return $ret;
    }
}
