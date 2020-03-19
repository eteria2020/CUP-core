<?php

namespace SharengoCore\Service\DatatableQueryBuilders;

class ExtraPayments implements DatatableQueryBuilderInterface
{
    /**
     * @var DatatableQueryBuilderInterface
     */
    private $queryBuilder;
    /**
     * @var string
     */
    private $joinType;

    public function __construct(DatatableQueryBuilderInterface $queryBuilder, $joinType = 'LEFT')
    {
        $this->queryBuilder = $queryBuilder;
        $this->joinType = $joinType;
    }

    public function select()
    {
        return $this->queryBuilder->select() . ', ep';
    }

    public function join()
    {
        return $this->queryBuilder->join() . $this->joinType . ' JOIN e.extrapayment ep ';
    }

    public function where()
    {
        return $this->queryBuilder->where();
    }
}
