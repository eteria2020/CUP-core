<?php

namespace SharengoCore\Service;

// Internals
use SharengoCore\Service\DatatableServiceInterface;
use SharengoCore\Service\DatatableQueryBuilders\DatatableQueryBuilderInterface;
// Externals
use Zend\Session\Container;

class SessionDatatableService implements DatatableServiceInterface
{
    /**
     * @var DatatableServiceInterface
     */
    private $datatableService;

    /**
     * @var Container
     */
    private $sessionContainer;

    /**
     * @param DatatableServiceInterface $datatableService
     * @param Container $sessionContainer
     */
    public function __construct(
        DatatableServiceInterface $datatableService,
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
    public function getData($entity, array $options, $count)
    {
        // Save the datatable filter options on a Session Contianer to maintain datatable
        // filters after page refresh.
        if (!empty($options)) {
            $this->sessionContainer->offsetSet($entity, $options);
        }

        // Translate Renamed Tables
        $entity = $entity === "TripsNotPayed" ? "Trips" : $entity;  

        return $this->datatableService->getData($entity, $options, $count); 
    }

    /**
     * @return DatatableQueryBuilderInterface
     */
    public function getQueryBuilder()
    {
        return $this->datatableService->getQueryBuilder();
    }

    /**
     * @param DatatableQueryBuilderInterface
     */
    public function setQueryBuilder(DatatableQueryBuilderInterface $queryBuilder)
    {
        $this->datatableService->setQueryBuilder($queryBuilder);
    }

}