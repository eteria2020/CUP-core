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
     * @param CustomerDeactivation $customerDeactivation
     */
    public function __construct(
        EntityManagerInterface $em,
        Customers $customer
    ) {
        parent::__construct($em);
        $this->params = [
            'cParam' => $customer()
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
            WHERE c = :cParam
            AND cd.startTs < now()
            AND (
                cd.endTs IS NULL
                OR cd.end > now()
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
