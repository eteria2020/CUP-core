<?php

namespace SharengoCore\Service;

use SharengoCore\Entity\Queries\AllFleets;
use SharengoCore\Entity\Fleet;
use SharengoCore\Exception\FleetNotFoundException;
use SharengoCore\Entity\Queries\FleetById;

use Doctrine\ORM\EntityManager;

class FleetService
{
    /**
     * @var EntityManager $entityManager
     */
    private $entityManager;

    /**
     * @param EntityManager $entityManager
     */
    public function __construct(
        EntityManager $entityManager
    ) {
        $this->entityManager = $entityManager;
    }

    /**
     * @return Penalties[]
     */
    public function getAllFleets()
    {
        $query = new AllFleets($this->entityManager);

        return $query();
    }

    public function getFleetsSelectorArray()
    {
        $fleets = [0 => '---'];

        foreach ($this->getAllFleets() as $fleet) {
            $fleets[$fleet->getId()] = $fleet->getName();
        }

        return $fleets;
    }

    /**
     * @param int $fleetId
     * @throws FleetNotFoundException
     * @return Fleet
     */
    public function getFleetById($fleetId)
    {
        $query = new FleetById($fleetId, $this->entityManager);

        $fleet = $query();

        if (! $fleet instanceof Fleet) {
            throw new FleetNotFoundException();
        }

        return $fleet;
    }
}
