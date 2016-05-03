<?php

namespace SharengoCore\Service;

use SharengoCore\Service\DatatableQueryBuilders\DatatableQueryBuilderInterface;

interface DatatableServiceInterface
{
    /**
     * Builds a query based on the entity and parameters passed and returns the
     * results. If $count is set to true, returns the COUNT() of the results.
     *
     * @param string $entity
     * @param array $options
     * @param boolean $count
     * @return mixed[] | integer
     */
    public function getData($entity, $options, $count);
    
    /**
     * @return DatatableQueryBuilderInterface
     */
    public function getQueryBuilder();

    /**
     * @param DatatableQueryBuilderInterface
     */
    public function setQueryBuilder(DatatableQueryBuilderInterface $queryBuilder);
}
