<?php

namespace SharengoCore\Entity\Queries;

use Doctrine\ORM\EntityManagerInterface;

class CustomerBonusForInvoice extends Query
{
    protected function dql()
    {
        return 'SELECT cb FROM \SharengoCore\Entity\CustomersBonus cb '.
            'WHERE cb.package IS NOT NULL '. // bonus comes from a package
            'AND cb.invoice IS NULL'; // bonus does not have an invoice yet
    }
}
