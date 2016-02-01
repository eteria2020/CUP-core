<?php

namespace SharengoCore\Entity\Queries;

use Doctrine\ORM\EntityManagerInterface;
use SharengoCore\Entity\Invoices;

class AllExtraPaymentTypes extends NativeQuery
{
    /**
     * @return string
     */
    protected function sql()
    {
        return "SELECT unnest(enum_range(NULL::extra_payments_types)) AS type
            ORDER BY type ASC";
    }

    /**
     * @return array
     */
    protected function scalarResults()
    {
        return [
            ['column_name' => 'type', 'alias' => 'type', 'type' => 'string']
        ];
    }
}
