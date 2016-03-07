<?php

namespace SharengoCore\Service\DatatableQueryBuilders;

class Cars implements DatatableQueryBuilderInterface
{
    /**
     * @var DatatableQueryBuilderInterface
     */
    private $queryBuilder;

    private $joinType;

    public function __construct(DatatableQueryBuilderInterface $queryBuilder, $joinType = 'LEFT')
    {
        $this->queryBuilder = $queryBuilder;
        $this->joinType = $joinType;
    }

    public function select()
    {
        return $this->queryBuilder->select() . ', c';
    }

    public function join()
    {
        return $this->queryBuilder->join() . $this->joinType . ' JOIN e.car c ';
    }

    public function where()
    {
        return $this->queryBuilder->where();
    }
}
