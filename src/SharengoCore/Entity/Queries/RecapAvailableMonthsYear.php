<?php

namespace SharengoCore\Entity\Queries;

use Doctrine\ORM\EntityManagerInterface;
use SharengoCore\Entity\Invoices;

class RecapAvailableMonthsYear extends NativeQuery
{
    /**
     * @var array
     */
    private $params = [];

    /**
     * @param EntityManagerInterface $em
     * @param \DateTime $start
     * @param \DateTime $end
     * @param string $format
     */
    
    public function __construct(EntityManagerInterface $em, $year)
    {
        parent::__construct($em);
        $this->params = [
            'year' => $year,
            'format' => 'MM'
        ];
    }
    
    /**
     * This query returns the months in which there are trip_payments or
     * subscription_payments. extra_payments and customers_bonus are ignored
     * to semplify the query since it is highly unlikely to have a month with
     * the latter but without the first two.
     *
     * It does this by first finding all the months in which there are
     * trip_payents, then finding all of those in which there are
     * subscription_payments and finally combining the results.
     *
     * Ordering is first done by year and then by month.
     *
     * @return string
     */
    protected function sql()
    {
        return "WITH tp AS (
                SELECT to_char(tp.payed_successfully_at, 'MM') AS date
                FROM trip_payments tp
                WHERE tp.payed_successfully_at IS NOT NULL AND to_char(tp.payed_successfully_at, 'YYYY') = :year
                GROUP BY date
            ), sp AS (
                SELECT to_char(t.datetime, 'MM') AS date
                FROM subscription_payments sp
                LEFT JOIN transactions t ON t.id = sp.transaction_id
                WHERE t.datetime IS NOT NULL AND to_char(t.datetime, 'YYYY') = :year
                GROUP BY date
            )
            SELECT DISTINCT COALESCE(tp.date, sp.date) AS date
            FROM tp
            FULL JOIN sp ON tp.date = sp.date
            ORDER BY date ASC";
    }

    /**
     * @return array
     */
    protected function scalarResults()
    {
        return [
            ['column_name' => 'date', 'alias' => 'date', 'type' => 'string']
        ];
    }

    /**
     * @return array
     */
    protected function params()
    {
        return $this->params;
    }
}
