<?php

namespace SharengoCore\Service\DatatableDecorators;

use SharengoCore\Service\DatatableServiceInterface;

class DatatableCarJoinService implements DatatableServiceInterface
{
    private $select;
    private $datatable;
    private $join;

    public function __construct(DatatableServiceInterface $datatable)
    {
        $this->datatable = $datatable;
        $this->setSelect($this->datatable->getSelect().', c');
        $this->setJoin($this->datatable->getJoin().'LEFT JOIN e.car c ');
    }

    /**
     * @inheritdoc
     */
    public function getData($entity, array $options)
    {
        $this->datatable->setSelect($this->getSelect());
        $this->datatable->setJoin($this->getJoin());
        return $this->datatable->getData($entity, $options);
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
