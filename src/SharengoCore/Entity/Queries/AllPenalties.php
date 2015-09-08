<?php

namespace SharengoCore\Entity\Queries;

final class AllPenalties extends Query
{
    protected $dql = 'SELECT p FROM \SharengoCore\Entity\Penalty p';
}
