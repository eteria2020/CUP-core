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
    public function getData($entity, array $options)
    {
        $dql = 'SELECT e FROM \SharengoCore\Entity\\'.$entity.' e ';
        $query = $this->entityManager->createQuery();

        if ($options['column'] != 'select' &&
            !empty($options['searchValue']) &&
            !empty($options['column'])
        ) {
            // if there is a selected filter, we apply it to the query
            if ($options['column'] == 'id') {
                $dql .= 'WHERE e.id = :id ';
                $query->setParameter('id', (int) $options['searchValue']);
            } else {
                $value = strtolower("%" . $options['searchValue'] . "%");
                $dql .= 'WHERE LOWER(e.'.$options['column'].') LIKE :value ';
                $query->setParameter('value', $value);
            }
        }

        // apply the requested ordering
        $orderFieldId = $options['iSortCol_0'];
        $orderField = $options['mDataProp_'.$orderFieldId];

        $dql .= 'ORDER BY e.'.$orderField.' '.$options['sSortDir_0'].' ';

        // limit and offset for pagination
        if ($options['withLimit']) {
            $query->setMaxResults($options['iDisplayLength']);
            $query->setFirstResult($options['iDisplayStart']);
        }

        $query->setDql($dql);

        return $query->getResult();
    }
}
