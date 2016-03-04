<?php

namespace SharengoCore\Service\DatatableQueryBuilders;

class CustomersNotGold implements DatatableQueryBuilderInterface
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
        return $this->queryBuilder->join().sprintf('INNER JOIN %s.customer cu ', $this->alias);
    }

    public function where()
    {
        $where = 'cu.goldList = false';
        if (strlen($this->queryBuilder->where()) > 0) {
            $where = ' AND ' . $where;
        }
        return $this->queryBuilder->where().$where;
    }
}
