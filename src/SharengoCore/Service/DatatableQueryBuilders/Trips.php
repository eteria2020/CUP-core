<?php

namespace SharengoCore\Service\DatatableQueryBuilders;

class Trips implements DatatableQueryBuilderInterface
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
        return $this->queryBuilder->select().', t';
    }

    public function join()
    {
        return $this->queryBuilder->join().'LEFT JOIN e.trip t ';
    }
}
