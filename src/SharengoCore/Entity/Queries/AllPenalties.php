<?php

namespace SharengoCore\Entity\Queries;

final class AllPenalties extends Query
{
    protected function dql()
    {
        return 'SELECT p FROM \SharengoCore\Entity\Penalty p';
    }
}
