<?php

namespace SharengoCore\Service\DatatableQueryBuilders;

class Zones implements DatatableQueryBuilderInterface
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
        return $this->queryBuilder->select();
    }

    public function join()
    {
        return '';
    }

    public function where()
    {
        return $this->queryBuilder->where();
    }
}
