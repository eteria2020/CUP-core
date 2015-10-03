<?php

namespace SharengoCore\Entity\Queries;

use Doctrine\ORM\EntityManagerInterface;

class FleetById extends Query
{
    /**
     * @param int
     */
    private $fleetId;

    public function __construct(
        $fleetId,
        EntityManagerInterface $em
    ) {
        $this->fleetId = $fleetId;

        parent::__construct($em);
    }

    protected function dql()
    {
        return 'SELECT f FROM \SharengoCore\Entity\Fleet f '.
            'WHERE f.id = :fleetId';
    }

    protected function params()
    {
        return [
            'fleetId' => $this->fleetId
        ];
    }

    protected function resultMethod()
    {
        return 'getOneOrNullResult';
    }
}
