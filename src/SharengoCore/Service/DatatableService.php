<?php

namespace SharengoCore\Service;

use SharengoCore\Service\DatatableServiceInterface;
use SharengoCore\Service\DatatableQueryBuilders\DatatableQueryBuilderInterface;

use Doctrine\ORM\EntityManager;

class DatatableService implements DatatableServiceInterface
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var DatatableQueryBuilderInterface
     */
    private $queryBuilder;

    /**
     * @param EntityManager $entityManager
     * @param DatatableQueryBuilderInterface $queryBuilder
     */
    public function __construct(
        EntityManager $entityManager,
        DatatableQueryBuilderInterface $queryBuilder
    ) {
        $this->entityManager = $entityManager;
        $this->queryBuilder = $queryBuilder;
    }

    /**
     * Builds a query based on the entity and parameters passed and returns the
     * results. If $count is set to true, returns the COUNT() of the results.
     *
     * @param string $entity
     * @param array $options
     * @param boolean $count
     * @return mixed[] | integer
     */
    public function getData($entity, array $options, $count = false)
    {
        $select = $this->queryBuilder->select();
        $join = $this->queryBuilder->join();
        $whereData = $this->queryBuilder->where();
        if (strlen($whereData) > 0) {
            $where = true;
            $whereData = 'WHERE ' . $whereData;
        } else {
            $where = false;
        }

        $as_parameters = [];

        $dql = 'SELECT ' . ($count ? 'COUNT(e)' : ('e' . $select)) . ' FROM \SharengoCore\Entity\\' . $entity . ' e '
            . $join . $whereData;

        $query = $this->entityManager->createQuery();

        if ($options['column'] != 'select' &&
            !empty($options['searchValue']) &&
            !empty($options['column'])
        ) {
            // if there is a selected filter, we apply it to the query
            $checkIdColumn = strpos($options['column'], '.id');

            if ($options['column'] == 'id' || $checkIdColumn) {
                $withAndWhere = $where ? 'AND ' : 'WHERE ';
                $dql .= $withAndWhere . $options['column'] . ' = :id ';
                $as_parameters['id'] = (int)$options['searchValue'];
            } else {
                $value = strtolower("%" . $options['searchValue'] . "%");
                $withAndWhere = $where ? 'AND ' : 'WHERE ';
                $dql .= $withAndWhere . ' LOWER(CAST(' . $options['column'] . ' as text)) LIKE :value ';
                $as_parameters['value'] = $value;
            }
            $where = true;
        }

        // query a fixed parameter
        if (!empty($options['fixedColumn']) &&
           !empty($options['fixedValue']) &&
           !empty($options['fixedLike'])
        ) {
            $withAndWhere = $where ? 'AND ' : 'WHERE ';
            $dql .= $withAndWhere . $options['fixedColumn'] . ' ';
            if ($options['fixedValue'] != null) {
                $dql .= ($options['fixedLike'] == 'true' ? 'LIKE ' : '= ') .
                ':fixedValue ';
                $as_parameters['fixedValue'] = $options['fixedValue'];
            } else {
                $dql .= 'IS NULL ';
            }
            $where = true;
        }

        //query with null
        if ($options['column'] != 'select' &&
            isset($options['columnNull']) &&
            !empty($options['columnNull'])
        ) {
            $withAndWhere = $where ? 'AND ' : 'WHERE ';
            $dql .= $withAndWhere . $options['columnNull'] . " IS NULL ";
            $where = true;
        }

        //query without LIKE operator
        if ($options['column'] != 'select' &&
            isset($options['columnWithoutLike']) &&
            !empty($options['columnWithoutLike']) &&
            !empty($options['columnValueWithoutLike'])
        ) {
            $withAndWhere = $where ? 'AND ' : 'WHERE ';
            $ValueWithoutLike = is_bool($options['columnValueWithoutLike']) ?
                $options['columnValueWithoutLike'] :
                sprintf("'%s'", $options['columnValueWithoutLike']);
            $dql .= $withAndWhere . $options['columnWithoutLike'] . " =  " . $ValueWithoutLike . " ";
            $where = true;
        }

        if (!empty($options['from']) &&
            !empty($options['to']) &&
            !empty($options['columnFromDate']) &&
            !empty($options['columnFromEnd'])
        ) {
            $withAndWhere = $where ? 'AND ' : 'WHERE ';
            $dql .= $withAndWhere . $options['columnFromDate'] . ' >= :from ';
            $dql .= 'AND ' . $options['columnFromEnd'] . ' <= :to ';

            $as_parameters['from'] = $options['from'] . ' 00:00:00';
            $as_parameters['to'] = $options['to'] . ' 23:59:00';
        }

        if (count($as_parameters) > 0) {
            $query->setParameters($as_parameters);
        }

        // cannot set order if using count.
        // might order by not selected field causing failure
        if (!$count) {
            // apply the requested ordering
            $orderFieldId = $options['iSortCol_0'];
            $orderField = $options['mDataProp_' . $orderFieldId];
            $dql .= 'ORDER BY ' . $orderField . ' ' . $options['sSortDir_0'] . ' ';
        }

        // limit and offset for pagination
        if ($options['withLimit']) {
            $query->setMaxResults($options['iDisplayLength']);
            $query->setFirstResult($options['iDisplayStart']);
        }

        $query->setDql($dql);

        if ($count) {
            return $query->getSingleScalarResult();
        }
        return $query->getResult();
    }

    /**
     * @return DatatableQueryBuilderInterface
     */
    public function getQueryBuilder()
    {
        return $this->queryBuilder;
    }

    /**
     * @param DatatableQueryBuilderInterface
     */
    public function setQueryBuilder(DatatableQueryBuilderInterface $queryBuilder)
    {
        $this->queryBuilder = $queryBuilder;
    }
}
