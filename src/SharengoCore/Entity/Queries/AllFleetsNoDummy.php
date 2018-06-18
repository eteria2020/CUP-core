<?php

namespace SharengoCore\Entity\Queries;

use SharengoCore\Entity\Fleet;

final class AllFleetsNoDummy extends Query {

    protected function dql() {
        return 'SELECT f FROM \SharengoCore\Entity\Fleet f WHERE f.id < ' . Fleet::DUMMY_FLEET_LIMIT . ' ORDER BY f.id';
    }

}
