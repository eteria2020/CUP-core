<?php

namespace SharengoCore\Service\DatatableQueryBuilders;

class TripPaymentNotPayed implements DatatableQueryBuilderInterface
{
    /**
     * @var DatatableQueryBuilderInterface
     */
    private $queryBuilder;

    public function __construct(DatatableQueryBuilderInterface $queryBuilder)
    {
        $this->queryBuilder = $queryBuilder;
    }

    public function select()
    {
        return $this->queryBuilder->select().', tp';
    }

    public function join()
    {
        return $this->queryBuilder->join().'INNER JOIN e.tripPayment tp ';
    }

    public function where()
    {
        $where = 'e.payable = true AND
        e.timestampEnd IS NOT NULL AND
        (e.timestampEnd - e.timestampBeginning) >= (DATE_ADD(CURRENT_TIMESTAMP(), 300, \'second\') - CURRENT_TIMESTAMP()) AND
        tp.payedSuccessfullyAt IS NULL ';
        if (strlen($this->queryBuilder->where()) > 0) {
            $where = ' AND ' . $where;
        }
        return $this->queryBuilder->where().$where;
    }
}