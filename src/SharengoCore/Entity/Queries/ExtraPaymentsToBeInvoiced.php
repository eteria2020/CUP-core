<?php

namespace SharengoCore\Entity\Queries;

class ExtraPaymentsToBeInvoiced extends Query
{
    protected function dql()
    {
        return 'SELECT e FROM \SharengoCore\Entity\ExtraPayments e '.
            'WHERE e.invoice IS NULL '.
            'AND e.invoiceAble = TRUE';
    }
}
