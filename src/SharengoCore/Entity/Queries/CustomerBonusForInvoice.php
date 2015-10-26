<?php

namespace SharengoCore\Entity\Queries;

use Doctrine\ORM\EntityManagerInterface;

class CustomerBonusForInvoice extends Query
{
    protected function dql()
    {
        return 'SELECT cb FROM \SharengoCore\Entity\CustomersBonus cb '.
            'JOIN cb.package p '. // customer bonus come from a package
            'WHERE cb.invoice IS NULL'; // customer bonus does not have an invoice yet
    }
}
