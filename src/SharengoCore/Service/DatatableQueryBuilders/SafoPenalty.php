<?php

namespace SharengoCore\Service\DatatableQueryBuilders;

class SafoPenalty implements DatatableQueryBuilderInterface
{
    /**
     * @var DatatableQueryBuilderInterface
     */
    private $queryBuilder;
    /**
     * @var string
     */
    //private $joinType;

    public function __construct(DatatableQueryBuilderInterface $queryBuilder)
    {
        $this->queryBuilder = $queryBuilder;
        //$this->joinType = $joinType;
    }

    public function select()
    {
        return $this->queryBuilder->select();
    }

    public function join()
    {
        return $this->queryBuilder->join();
    }

    public function where()
    {
        return $this->queryBuilder->where();
    }
}
