<?php

namespace SharengoCore\Entity\Queries;

final class AllFleets extends Query
{
    protected function dql()
    {
        return 'SELECT f FROM \SharengoCore\Entity\Fleet f ORDER BY f.id';
    }
}
