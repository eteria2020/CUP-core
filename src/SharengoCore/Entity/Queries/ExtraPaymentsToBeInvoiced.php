<?php

namespace SharengoCore\Entity\Queries;

class ExtraPaymentsToBeInvoiced extends Query
{
    protected function dql()
    {
        return 'SELECT e FROM \SharengoCore\Entity\ExtraPayment e '.
            'WHERE e.invoice IS NULL '.
            'AND e.invoiceAble = TRUE';
    }
}
