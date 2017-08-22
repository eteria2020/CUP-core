<?php

namespace SharengoCore\Entity\Queries;

final class AllAddPoint extends Query
{
    protected function dql()
    {
        return 'SELECT a FROM \SharengoCore\Entity\AddBonus a';
    }
}
