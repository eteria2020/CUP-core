<?php

namespace SharengoCore\Service\DatatableQueryBuilders;

class Fleets implements DatatableQueryBuilderInterface
{
    /**
     * @var DatatableQueryBuilderInterface
     */
    private $queryBuilder;

    public function __construct(DatatableQueryBuilderInterface $queryBuilder)
    {
        $this->queryBuilder = $queryBuilder;
    }

    public function select()
    {
        return $this->queryBuilder->select().', f';
    }

    public function join()
    {
        return $this->queryBuilder->join().'LEFT JOIN e.fleet f ';
    }
}
