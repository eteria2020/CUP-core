<?php

namespace SharengoCore\Service;

use Doctrine\ORM\EntityManager;

class DatatableService
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @var string name of the entity
     * @var array options for the query
     * @return array of entities
     */
    public function getData($entity, array $options, array $joinTable = [])
    {
        $select = '';
        $join = '';
        $comma = ',';
        $where = false;
        $as_parameters = array();

        if (in_array('car', $joinTable)) {
            $select .= $comma.'c';
            $join .= 'LEFT JOIN e.car c ';
        }

        if (in_array('customer', $joinTable)) {
            $select .= $comma.'cu ';
            $join .= 'LEFT JOIN e.customer cu ';
        }

        $dql = 'SELECT e' . $select . ' FROM \SharengoCore\Entity\\' . $entity . ' e ';
        $dql .= $join;

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

        if(count($as_parameters) > 0) {
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
}
