<?php

namespace SharengoCore\Service\DatatableQueryBuilders;

class Cards implements DatatableQueryBuilderInterface
{
    /**
     * @var DatatableQueryBuilderInterface
     */
    private $queryBuilder;

    private $alias;

    public function __construct(DatatableQueryBuilderInterface $queryBuilder, $alias)
    {
        $this->queryBuilder = $queryBuilder;
        $this->alias = $alias;
    }

    public function select()
    {
        return $this->queryBuilder->select().', cc';
    }

    public function join()
    {
        return $this->queryBuilder->join().sprintf('LEFT JOIN %s.card cc ', $this->alias);
    }
}
