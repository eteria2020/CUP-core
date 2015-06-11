<?php

namespace SharengoCore\Service\DatatableQueryBuilders;

class Customers implements DatatableQueryBuilderInterface
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
        return $this->queryBuilder->select().', cu';
    }

    public function join()
    {
        return $this->queryBuilder->join().'LEFT JOIN e.customer cu ';
    }
}
