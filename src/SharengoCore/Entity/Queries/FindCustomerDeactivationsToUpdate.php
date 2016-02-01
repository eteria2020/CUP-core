<?php

namespace SharengoCore\Entity\Queries;

use SharengoCore\Entity\CustomerDeactivation;

use Doctrine\ORM\EntityManagerInterface;

class FindCustomerDeactivationsToUpdate extends Query
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
        CustomerDeactivation $customerDeactivation
    ) {
        parent::__construct($em);
        $this->params = [
            'deactivation' => $customerDeactivation,
            'customerParam' => $customerDeactivation->getCustomer(),
            'reasonParam' => $customerDeactivation->getReason()
        ];
    }

    /**
     * @return string
     */
    protected function dql()
    {
        return "SELECT cd
            FROM SharengoCore\Entity\CustomerDeactivation cd
            JOIN cd.customer c
            WHERE cd != :deactivation
            AND c = :customerParam
            AND cd.reason = :reasonParam
            AND cd.endTs IS NULL";
    }

    /**
     * @return array
     */
    protected function params()
    {
        return $this->params;
    }
}
