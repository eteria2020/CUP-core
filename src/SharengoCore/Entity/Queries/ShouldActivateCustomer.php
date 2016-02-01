<?php

namespace SharengoCore\Entity\Queries;

use SharengoCore\Entity\CustomerDeactivation;
use SharengoCore\Entity\Customers;

use Doctrine\ORM\EntityManagerInterface;

class ShouldActivateCustomer extends Query
{
    /**
     * @var array
     */
    private $params = [];

    /**
     * Checks if Customer has active deactivations. It is possible to give a
     * CustomerDeactivation that will be ignored in the check
     *
     * @param EntityManagerInterface $em
     * @param Customers $customerDeactivation
     * @param CustomerDeactivation|null $deactivation
     */
    public function __construct(
        EntityManagerInterface $em,
        Customers $customer,
        CustomerDeactivation $deactivation = null
    ) {
        parent::__construct($em);
        $this->params = [
            'customerParam' => $customer
        ];
        if ($deactivation !== null) {
            $this->params['deactivationParam'] = $deactivation;
        }
    }

    /**
     * @return boolean wether there are no other CustomerDeactivations active
     * on the same Customer
     */
    public function __invoke()
    {
        $result = parent::__invoke();
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
                OR cd.endTs > CURRENT_TIMESTAMP()
            )" .
            (array_key_exists('deactivationParam', $this->params) ? ' AND cd != :deactivationParam' : '');
    }

    /**
     * @return array
     */
    protected function params()
    {
        return $this->params;
    }
}
