<?php

namespace SharengoCore\Entity\Queries;

use SharengoCore\Entity\Customers;

use Doctrine\ORM\EntityManagerInterface;

class FindActiveCustomerDeactivations extends Query
{
    /**
     * @var array
     */
    private $params = [];

    /**
     * @var string
     */
    private $resultMethod = 'getResult';

    /**
     * @param EntityManagerInterface $em
     * @param Customers $customer
     * @param string|null $reason
     */
    public function __construct(
        EntityManagerInterface $em,
        Customers $customer,
        $reason = null
    ) {
        parent::__construct($em);
        $this->params = [
            'customerParam' => $customer
        ];
        if ($reason !== null) {
            $this->params['reasonParam'] = $reason;
            $this->resultMethod = 'getOneOrNullResult';
        }
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
                OR cd.endTs > CURRENT_TIMESTAMP()
            )" .
            (array_key_exists('reasonParam', $this->params) ? ' AND cd.reason = :reasonParam' : '');
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
        return $this->resultMethod;
    }
}
