<?php

namespace SharengoCore\Service\DatatableQueryBuilders;

class NotificationsProtocols implements DatatableQueryBuilderInterface
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
        return $this->queryBuilder->select() . ', np';
    }

    public function join()
    {
        return $this->queryBuilder->join() . $this->joinType . ' JOIN e.protocol np ';
    }

    public function where()
    {
        return $this->queryBuilder->where();
    }
}
