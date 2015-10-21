<?php

namespace SharengoCore\Entity\Queries;

use Doctrine\ORM\EntityManagerInterface;

class FleetByCode extends Query
{
    /**
     * @param int
     */
    private $fleetCode;

    public function __construct(
        $fleetCode,
        EntityManagerInterface $em
    ) {
        $this->fleetCode = $fleetCode;

        parent::__construct($em);
    }

    protected function dql()
    {
        return 'SELECT f FROM \SharengoCore\Entity\Fleet f '.
            'WHERE f.code = :fleetCode';
    }

    protected function params()
    {
        return [
            'fleetCode' => $this->fleetCode
        ];
    }

    protected function resultMethod()
    {
        return 'getOneOrNullResult';
    }
}
