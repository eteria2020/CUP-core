<?php

namespace SharengoCore\Service;

use SharengoCore\Entity\Queries\ExtraPaymentsToBeInvoiced;

use Doctrine\ORM\EntityManager;

class ExtraPaymentsSearchService
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    public function __construct(
        EntityManager $entityManager
    ) {
        $this->entityManager = $entityManager;
    }

    public function getExtraPaymentsForInvoice()
    {
        $query = new ExtraPaymentsToBeInvoiced($this->entityManager);

        return $query();
    }
}
