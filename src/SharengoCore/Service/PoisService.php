<?php

namespace SharengoCore\Service;

use SharengoCore\Entity\Repository\PoisRepository;

class PoisService
{
    /**
     * @var PoisRepository
     */
    private $poisRepository;

    /**
     * @param PoisRepository
     */
    public function __construct(PoisRepository $poisRepository)
    {
        $this->poisRepository = $poisRepository;
    }

    /**
     * @return mixed
     */
    public function getListPois()
    {
        return $this->poisRepository->findAll();
    }

}
