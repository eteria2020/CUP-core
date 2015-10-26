<?php

namespace SharengoCore\Entity\Queries;

use Cartasi\Entity\Transactions;

use Doctrine\ORM\EntityManagerInterface;

class CustomerBonusByTransaction extends Query
{
    /**
     * @var Transactions
     */
    private $tranasction;

    public function __construct(
        Transactions $transaction,
        EntityManagerInterface $entityManager
    ) {
        $this->transaction = $transaction;

        parent::__construct($entityManager);
    }

    protected function dql()
    {
        return 'SELECT cb FROM \SharengoCore\Entity\CustomersBonus cb '.
            'WHERE cb.transaction = :transaction';
    }

    protected function params()
    {
        return [
            'transaction' => $this->transaction
        ];
    }

    protected function resultMethod()
    {
        return 'getOneOrNullResult';
    }
}
