<?php

namespace SharengoCore\Entity\Queries;

use Doctrine\ORM\EntityManagerInterface;
use SharengoCore\Entity\Invoices;

class RecapAvailableMonths extends NativeQuery
{
    protected function sql()
    {
        return "SELECT to_char(tp.payed_successfully_at, :format) AS date
            FROM trip_payments tp
            WHERE tp.payed_successfully_at IS NOT NULL
            GROUP BY date
            ORDER BY date DESC";
    }

    protected function scalarResults()
    {
        return [
            ['column_name' => 'date', 'alias' => 'date', 'type' => 'string']
        ];
    }

    protected function params()
    {
        return ['format' => 'MM-YYYY'];
    }
}
