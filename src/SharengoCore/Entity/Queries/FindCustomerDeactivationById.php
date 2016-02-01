<?php

namespace SharengoCore\Entity\Queries;

use Doctrine\ORM\EntityManagerInterface;

class FindCustomerDeactivationById extends Query
{
    /**
     * @var array
     */
    private $params = [];

    /**
     * @param EntityManagerInterface $em
     * @param integer $id
     */
    public function __construct(
        EntityManagerInterface $em,
        $id
    ) {
        parent::__construct($em);
        $this->params = [
            'idParam' => $id
        ];
    }

    /**
     * @return string
     */
    protected function dql()
    {
        return "SELECT cd
            FROM SharengoCore\Entity\CustomerDeactivation cd
            WHERE cd.id = :idParam";
    }

    /**
     * @return array
     */
    protected function params()
    {
        return $this->params;
    }

    /**
     * @return string
     */
    protected function resultMethod()
    {
        return 'getOneOrNullResult';
    }
}
