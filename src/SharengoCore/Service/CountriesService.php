<?php

namespace SharengoCore\Service;

use SharengoCore\Entity\Countries;
use SharengoCore\Entity\Repository\CountriesRepository;

class CountriesService
{
    private $repository;

    public function __construct(CountriesRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getAllCountries($selectedCountry = null)
    {
        $countries = $this->repository->getAllCountries($selectedCountry);
        $ret = [];

        foreach ($countries as $c) {
            $ret[$c['code']] = html_entity_decode($c['name']);
        }

        return $ret;
    }

    /**
     * @param null $selectedCountry
     * @return array
     */
    public function getAllPhoneCodeByCountry($selectedCountry = null)
    {
        $countries = $this->repository->getAllPhoneCodeByCountry($selectedCountry);
        $ret = [];

        foreach ($countries as $c) {
            if($c['code']!=='VA') { // skip "Citta del Vaticano" because has phone code 39 the same of Italy
                $ret[$c['phoneCode']] = html_entity_decode($c['name']."(+".$c['phoneCode'].")");
            }
            if(count($ret)==1) {    // put in second place the string '----------' and value 'disabled'
                $ret['disabled'] = html_entity_decode("----------");
            }
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

    /**
     * @param string
     * @return Countries
     */
    public function getCountryByName($name)
    {
        return $this->repository->findOneByName($name);
    }
}
