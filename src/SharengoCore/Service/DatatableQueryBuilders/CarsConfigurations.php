<?php

namespace SharengoCore\Service\DatatableQueryBuilders;

class CarsConfigurations implements DatatableQueryBuilderInterface
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
        return $this->queryBuilder->select().', cc';
    }

    public function join()
    {
        return $this->queryBuilder->join().'LEFT JOIN e.cars_configurations cc ';
    }
}
