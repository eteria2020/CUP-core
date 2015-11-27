<?php

namespace SharengoCore\Entity\Queries;

use SharengoCore\Entity\Customers;

use Doctrine\ORM\EntityManagerInterface;

class ShouldActivateCustomer extends Query
{
    /**
     * @var array
     */
    private $params = [];

    /**
     * @param EntityManagerInterface $em
     * @param Customers $customerDeactivation
     */
    public function __construct(
        EntityManagerInterface $em,
        Customers $customer
    ) {
        parent::__construct($em);
        $this->params = [
            'customerParam' => $customer
        ];
    }

    /**
     * @return boolean wether there are no other CustomerDeactivations active
     * on the same Customer
     */
    public function __invoke()
    {
        $result = parent::__invoke;
        return empty($result);
    }

    /**
     * @return string
     */
    protected function dql()
    {
        return "SELECT cd
            FROM SharengoCore\Entity\CustomerDeactivation cd
            JOIN cd.customer c
            WHERE c = :customerParam
            AND cd.startTs < CURRENT_TIMESTAMP()
            AND (
                cd.endTs IS NULL
                OR cd.end > CURRENT_TIMESTAMP()
            )";
    }

    /**
     * @return array
     */
    protected function params()
    {
        return $this->params;
    }
}
