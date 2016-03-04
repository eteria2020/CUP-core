<?php

namespace SharengoCore\Service\DatatableQueryBuilders;

class Cards implements DatatableQueryBuilderInterface
{
    /**
     * @var DatatableQueryBuilderInterface
     */
    private $queryBuilder;

    private $alias;

    private $joinType;

    public function __construct(DatatableQueryBuilderInterface $queryBuilder, $alias, $joinType = 'LEFT')
    {
        $this->queryBuilder = $queryBuilder;
        $this->alias = $alias;
        $this->joinType = $joinType;
    }

    public function select()
    {
        return $this->queryBuilder->select().', cc';
    }

    public function join()
    {
        return $this->queryBuilder->join().sprintf($this->joinType . ' JOIN %s.card cc ', $this->alias);
    }

    public function where()
    {
        return $this->queryBuilder->where();
    }
}
