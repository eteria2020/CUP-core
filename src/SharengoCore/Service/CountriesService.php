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
            $ret[$c['code']] = html_entity_decode($c['name']);
        }

        return $ret;
    }

    /**
     * @param string
     * @return string
     */
    public function getMctcCode($code)
    {
        $country = $this->repository->findOneByCode($code);

        return $country->getMctc();
    }

    /**
     * @param string
     * @return string
     */
    public function getCountryByCadastralCode($code)
    {
        $country = $this->repository->findOneByCadastralCode($code);
        if(is_null($country)){
            return 'it'; //default value in case some cadastral codes are missing in the countries table
        }
        return $country->getCode();
    }
}
