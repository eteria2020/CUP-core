<?php

namespace SharengoCore\Service\DatatableQueryBuilders;

class Customers implements DatatableQueryBuilderInterface
{
    /**
     * @var DatatableQueryBuilderInterface
     */
    private $queryBuilder;

    public function __construct(DatatableQueryBuilderInterface $queryBuilder, $alias = 'e')
    {
        $this->queryBuilder = $queryBuilder;
        $this->alias = $alias;
    }

    public function select()
    {
        return $this->queryBuilder->select().', cu';
    }

    public function join()
    {
        return $this->queryBuilder->join().sprintf('LEFT JOIN %s.customer cu ', $this->alias);
    }
}
