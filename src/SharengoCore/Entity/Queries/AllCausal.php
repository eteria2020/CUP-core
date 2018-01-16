<?php

namespace SharengoCore\Entity\Queries;

final class AllCausal extends Query
{
    protected function dql()
    {
        return 'SELECT p FROM \SharengoCore\Entity\Penalty p '
        . 'WHERE p.type = :type';
    }
    
    protected function params()
    {
        return [
            'type' => 'extra'
        ];
    }
}
