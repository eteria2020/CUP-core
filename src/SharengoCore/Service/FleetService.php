<?php

namespace SharengoCore\Service;

use SharengoCore\Entity\Fleet;
use SharengoCore\Exception\FleetNotFoundException;
use SharengoCore\Entity\Customers;
use SharengoCore\Entity\Queries\AllFleets;
use SharengoCore\Entity\Queries\FleetById;
use SharengoCore\Entity\Queries\FleetByCode;
use SharengoCore\Entity\Queries\DefaultFleet;
use Doctrine\ORM\EntityManager;

class FleetService {

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
    public function getAllFleets() {
        $query = new AllFleets($this->entityManager);

        return $query();
    }

    /**
     * This method return an array of all Fleets name, indexed with the object
     * key "id", to be used with a ZF2 Form Select.
     * With the first parameter for example you can specify frirst element/s.
     *
     * @param array $fleets The inital "empty" array object.
     */
    public function getFleetsSelectorArray(array $fleets = []) {
        foreach ($this->getAllFleets() as $fleet) {
            $fleets[$fleet->getId()] = $fleet->getName();
        }

        return $fleets;
    }

    /**
     * This method return an array of Fleets name with id<DUMMY_FLEET_LIMIT, 
     * indexed with the object key "id", to be used with a ZF2 Form Select.
     * With the first parameter for example you can specify frirst element/s.
     *
     * @param array $fleets The inital "empty" array object.
     */
    public function getFleetsSelectorArrayNoDummy(array $fleets = []) {
        foreach ($this->getAllFleets() as $fleet) {
            if ($fleet->getId() < Fleet::DUMMY_FLEET_LIMIT) {
                $fleets[$fleet->getId()] = $fleet->getName();
            }
        }

        return $fleets;
    }

    /**
     * @param int $fleetId
     * @throws FleetNotFoundException
     * @return Fleet
     */
    public function getFleetById($fleetId) {
        $query = new FleetById($fleetId, $this->entityManager);

        $fleet = $query();

        if (!$fleet instanceof Fleet) {
            throw new FleetNotFoundException();
        }

        return $fleet;
    }

    /**
     * @param Customers|null $customer
     * @return Fleet
     */
    public function getCustomerOrDefaultFleet(Customers $customer = null) {
        if ($customer instanceof Customers) {
            return $customer->getFleet();
        }

        return $this->getDefaultFleet();
    }

    /**
     * @return Fleet
     */
    private function getDefaultFleet() {
        $query = new DefaultFleet($this->entityManager);

        return $query();
    }

    /**
     * @param string $fleetCode
     * @return Fleet
     */
    public function getFleetByCode($fleetCode) {
        $query = new FleetByCode($fleetCode, $this->entityManager);

        $fleet = $query();

        if (!$fleet instanceof Fleet) {
            throw new FleetNotFoundException();
        }

        return $fleet;
    }

}
