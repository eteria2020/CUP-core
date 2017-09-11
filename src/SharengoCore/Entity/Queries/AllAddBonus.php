<?php

namespace SharengoCore\Entity\Queries;

final class AllAddBonus extends Query
{
    protected function dql()
    {
        return "SELECT a FROM \SharengoCore\Entity\AddBonus a WHERE a.type='min'";
    }
}
