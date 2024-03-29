<?php

namespace SharengoCore\Service\DatatableQueryBuilders;

class CarsInfo implements DatatableQueryBuilderInterface
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
        return $this->queryBuilder->select().', ci';
    }

    public function join()
    {
        return $this->queryBuilder->join().'LEFT JOIN e.carsInfo ci ';
    }

    public function where()
    {
        return $this->queryBuilder->where();
    }
}
