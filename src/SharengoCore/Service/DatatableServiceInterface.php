<?php

namespace SharengoCore\Service;

interface DatatableServiceInterface
{

    /**
     * @var string name of the entity
     * @var array options for the query
     * @return array of entities
     */
    public function getData($entity, array $options);

    /**
     * @return string
     */
    public function getSelect();

    /**
     * @var string
     */
    public function setSelect($select);

    /**
     * @return string
     */
    public function getJoin();

    /**
     * @var string
     */
    public function setJoin($join);
}
