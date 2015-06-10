<?php

namespace SharengoCore\Service;

use SharengoCore\Service\DatatableQueryBuilders\DatatableQueryBuilderInterface;

use Doctrine\ORM\EntityManager;

class DatatableService
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var DatatableQueryBuilderInterface
     */
    private $queryBuilder;

    public function __construct(
        EntityManager $entityManager,
        DatatableQueryBuilderInterface $queryBuilder
    ) {
        $this->entityManager = $entityManager;
        $this->queryBuilder = $queryBuilder;
    }

    /**
     * @inheritdoc
     */
    public function getData($entity, array $options/*, array $joinTable = []*/)
    {
        $select = $this->queryBuilder->select();
        $join = $this->queryBuilder->join();
        $where = false;
        $as_parameters = array();

        $dql = 'SELECT e' . $select . ' FROM \SharengoCore\Entity\\' . $entity . ' e '
            . $join;

        $query = $this->entityManager->createQuery();

        if ($options['column'] != 'select' &&
            !empty($options['searchValue']) &&
            !empty($options['column'])
        ) {

            // if there is a selected filter, we apply it to the query
            $checkIdColumn = strpos($options['column'], 'id');

            if ($options['column'] == 'id' || $checkIdColumn) {
                $dql .= 'WHERE ' . $options['column'] . ' = :id ';
                $as_parameters['id'] = (int)$options['searchValue'];
                $where = true;

            } else {
                $value = strtolower("%" . $options['searchValue'] . "%");
                $dql .= 'WHERE LOWER(' . $options['column'] . ') LIKE :value ';
                $as_parameters['value'] = $value;
                $where = true;
            }
        }

        if (!empty($options['from']) &&
            !empty($options['to']) &&
            !empty($options['columnFromDate']) &&
            !empty($options['columnFromEnd'])) {
            $withAndWhere = $where ? 'AND ' : 'WHERE ';
            $dql .= $withAndWhere . $options['columnFromDate'] . ' >= :from ';
            $dql .= 'AND ' . $options['columnFromEnd'] . ' <= :to ';

            $as_parameters['from'] = $options['from'] . ' 00:00:00';
            $as_parameters['to'] = $options['to'] . ' 23:59:00';
        }

        if (count($as_parameters) > 0) {
            $query->setParameters($as_parameters);
        }

        // apply the requested ordering
        $orderFieldId = $options['iSortCol_0'];
        $orderField = $options['mDataProp_' . $orderFieldId];

        $dql .= 'ORDER BY e.' . $orderField . ' ' . $options['sSortDir_0'] . ' ';

        // limit and offset for pagination
        if ($options['withLimit']) {
            $query->setMaxResults($options['iDisplayLength']);
            $query->setFirstResult($options['iDisplayStart']);
        }

        $query->setDql($dql);
        return $query->getResult();
    }

    /**
     * @ret DatatableQueryBuilderInterface
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
