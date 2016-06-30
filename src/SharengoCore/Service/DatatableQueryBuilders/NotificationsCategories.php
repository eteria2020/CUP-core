<?php

namespace SharengoCore\Service\DatatableQueryBuilders;

class NotificationsCategories implements DatatableQueryBuilderInterface
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
        return $this->queryBuilder->select() . ', nc';
    }

    public function join()
    {
        return $this->queryBuilder->join() . $this->joinType . ' JOIN e.category nc ';
    }

    public function where()
    {
        return $this->queryBuilder->where();
    }
}
