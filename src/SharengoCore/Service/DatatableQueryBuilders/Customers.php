<?php

namespace SharengoCore\Service\DatatableQueryBuilders;

class Customers implements DatatableQueryBuilderInterface
{
    /**
     * @var DatatableQueryBuilderInterface
     */
    private $queryBuilder;

    private $joinType;

    public function __construct(DatatableQueryBuilderInterface $queryBuilder, $alias = 'e', $joinType = 'LEFT')
    {
        $this->queryBuilder = $queryBuilder;
        $this->alias = $alias;
        $this->joinType = $joinType;
    }

    public function select()
    {
        return $this->queryBuilder->select() . ', cu';
    }

    public function join()
    {
        return $this->queryBuilder->join() . sprintf($this->joinType . ' JOIN %s.customer cu ', $this->alias);
    }

    public function where()
    {
        return $this->queryBuilder->where();
    }
}
