<?php

namespace SharengoCore\Service;

use SharengoCore\Entity\Authority;
use SharengoCore\Entity\Repository\AuthorityRepository;
use SharengoCore\Exception\InvalidAuthorityCodeException;

class AuthorityService
{
    private $repository;

    public function __construct(AuthorityRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getAllAuthorities()
    {
        $countries = $this->repository->getAllAuthorities();
        $ret = [];

        foreach ($countries as $c) {
            $ret[$c['code']] = $c['name'];
        }

        return $ret;
    }

    /**
     * @param string
     * @return Authority
     * @throws InvalidAuthorityCodeException if the code does not correspond to
     *  an actual authority
     */
    public function getByCode($code)
    {
        $authority = $this->repository->findOneByCode($code);

        if (!$authority instanceof Authority) {
            throw new InvalidAuthorityCodeException();
        }

        return $authority;
    }
}
