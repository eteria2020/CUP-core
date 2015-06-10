<?php

namespace SharengoCore\Service;

use Doctrine\ORM\EntityManager;

class DatatableService implements DatatableServiceInterface
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    private $select = '';

    private $join = '';

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @inheritdoc
     */
    public function getData($entity, array $options/*, array $joinTable = []*/)
    {
        $where = false;
        $as_parameters = array();

        $dql = 'SELECT e' . $this->select . ' FROM \SharengoCore\Entity\\' . $entity . ' e '
            . $this->join;

        $query = $this->entityManager->createQuery();

        if ($options['column'] != 'select' &&
            !empty($options['searchValue']) &&
            !empty($options['column'])
        ) {

            // if there is a selected filter, we apply it to the query
            $checkIdColumn = strpos($options['column'], 'id');

            if ($options['column'] == 'id' || $checkIdColumn) {
                $dql .= 'WHERE e.' . $options['column'] . ' = :id ';
                $as_parameters['id'] = (int)$options['searchValue'];
                $where = true;

            } else {
                $value = strtolower("%" . $options['searchValue'] . "%");
                $dql .= 'WHERE LOWER(e.' . $options['column'] . ') LIKE :value ';
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

    /**
     * @inheritdoc
     */
    public function getSelect()
    {
        return $this->select;
    }

    /**
     * @inheritdoc
     */
    public function setSelect($select)
    {
        $this->select = $select;
    }

    /**
     * @inheritdoc
     */
    public function getJoin()
    {
        return $this->join;
    }

    /**
     * @inheritdoc
     */
    public function setJoin($join)
    {
        $this->join = $join;
    }
}
