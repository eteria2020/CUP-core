<?php

namespace SharengoCore\Entity\Queries;

use Doctrine\ORM\EntityManagerInterface;

class CustomerBonusForInvoice extends Query
{
    protected function dql()
    {
        return 'SELECT cb FROM \SharengoCore\Entity\CustomersBonus cb '.
            'JOIN cb.package p '. // bonus came from a package
            'WHERE cb.invoice IS NULL'; // bonus does not have an invoice yet
    }
}
