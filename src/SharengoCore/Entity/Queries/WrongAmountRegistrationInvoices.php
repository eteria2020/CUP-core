<?php

namespace SharengoCore\Entity\Queries;

use SharengoCore\Entity\Invoices;

class WrongAmountRegistrationInvoices extends Query
{
    protected function dql()
    {
        return 'SELECT i as invoice, sp.amount FROM \SharengoCore\Entity\Invoices i '.
            'JOIN \SharengoCore\Entity\SubscriptionPayment sp '.
            'WITH i.customer = sp.customer '.
            'WHERE i.type = :type '.
            'AND i.amount != sp.amount';
    }

    protected function params()
    {
        return [
            'type' => Invoices::TYPE_FIRST_PAYMENT
        ];
    }
}
