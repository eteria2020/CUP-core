<?php

namespace SharengoCore\Service;

use SharengoCore\Entity\Repository\AuthorityRepository;

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
}