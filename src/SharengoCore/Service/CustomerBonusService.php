<?php

namespace SharengoCore\Service;

use SharengoCore\Entity\Queries\CustomerBonusByTransaction;
use Cartasi\Entity\Transactions;
use SharengoCore\Entity\CustomersBonus;

use Doctrine\ORM\EntityManagerInterface;

class CustomerBonusService
{
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function transactionAlreadyUsed(Transactions $transaction)
    {
        $query = new CustomerBonusByTransaction($transaction, $this->entityManager);

        return $query() instanceof CustomersBonus;
    }
}
