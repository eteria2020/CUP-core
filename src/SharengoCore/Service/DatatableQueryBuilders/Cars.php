<?php

namespace SharengoCore\Service\DatatableQueryBuilders;

class Cars implements DatatableQueryBuilderInterface
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
        return $this->queryBuilder->select().', c';
    }

    public function join()
    {
        return $this->queryBuilder->join().'LEFT JOIN e.car c ';
    }
}
