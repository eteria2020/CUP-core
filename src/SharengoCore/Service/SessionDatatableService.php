<?php

namespace SharengoCore\Service;

use SharengoCore\Service\DatatableService;

class SessionDatatableService
{
    /**
     * @var DatatableService
     */
    private $datatableService;

    /**
     * @var Container
     */
    private $sessionContainer;

    /**
     * @param DatatableService $datatableService
     * @param Container $sessionContainer
     */
    public function __construct(
        DatatableService $datatableService,
        Container $sessionContainer
    ) {
        $this->sessionContainer = $sessionContainer;
        $this->datatableService = $datatableService;
    }

    /**
     * Builds a query based on the entity and parameters passed and returns the
     * results. If $count is set to true, returns the COUNT() of the results.
     * 
     * [Decoration] Save the filter options on a Session Container  
     *
     * @param string $entity
     * @param array $options
     * @param boolean $count
     * @return mixed[] | integer
     */
    public function getData($entity, array $options, $count = false)
    {
        // Save the datatable filter options on a Session Contianer to maintain datatable
        // filters after page refresh.
        if (!empty($options)) {
            $sessionContainer->offsetSet($this->params['datatableFilters'][$entity], $options);
        }

        return $datatableService->getData($entity, $options, $count); 
    }

    /**
     * @return DatatableQueryBuilderInterface
     */
    public function getQueryBuilder()
    {
        return $datatableService->getQueryBuilder();
    }

    /**
     * @param DatatableQueryBuilderInterface
     */
    public function setQueryBuilder(DatatableQueryBuilderInterface $queryBuilder)
    {
        $datatableService->setQueryBuilder($queryBuilder);
    }
}