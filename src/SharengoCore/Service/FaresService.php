<?php

namespace SharengoCore\Service;

use SharengoCore\Entity\Fares;
use SharengoCore\Entity\Repository\FaresRepository;

class FaresService
{
    /**
     * @var FaresRepository
     */
    private $faresRepository;

    public function __construct(FaresRepository $faresRepository)
    {
        $this->faresRepository = $faresRepository;
    }

    /**
     * at the moment there is only one fare, so we return that one. When there
     * will be more than one fare, this will have input parameters to decide
     * which fare to consider
     *
     * @return Fares
     */
    public function getFare()
    {
        return $this->faresRepository->findOne();
    }
}
