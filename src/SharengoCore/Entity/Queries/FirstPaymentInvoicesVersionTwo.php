<?php

namespace SharengoCore\Entity\Queries;

use SharengoCore\Entity\Invoices;

class FirstPaymentInvoicesVersionTwo extends Query
{
    protected function dql()
    {
        return 'SELECT i FROM \SharengoCore\Entity\Invoices i '.
            'WHERE i.version = :version '.
            'AND i.type = :type';
    }

    protected function params()
    {
        return [
            'version' => 2,
            'type' => Invoices::TYPE_FIRST_PAYMENT
        ];
    }
}
