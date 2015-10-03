<?php

namespace SharengoCore\Entity\Queries;

use Doctrine\ORM\EntityManagerInterface;

final class DefaultFleet extends Query
{
    protected function dql()
    {
        return 'SELECT f FROM \SharengoCore\Entity\Fleet f '.
            'WHERE f.isDefault = true';
    }

    protected function resultMethod()
    {
        return 'getSingleResult';
    }
}
