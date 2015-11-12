<?php

namespace SharengoCore\Entity\Queries;

use Doctrine\ORM\EntityManagerInterface;
use SharengoCore\Entity\Invoices;

class PayedBetween extends NativeQuery
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
    public function __construct(EntityManagerInterface $em, \Datetime $start, \Datetime $end, $format)
    {
        parent::__construct($em);
        $this->params = [
            'format' => $format,
            'start' => $start->format('Y-m-d H:i:s'),
            'end' => $end->format('Y-m-d H:i:s')
        ];
    }

    /**
     * This query is divided in one main query and 4 "subqueries".
     * Each of the 4 subqueries collects data for a specific kind of payment.
     * The data is organized in three fields, one for the date, one for the
     * fleet and one for the amount.
     *
     * - tp_data referres to trip_payments
     * - sp_data referres to subscription_payments
     * - ep_data referres to extra_payments
     * - cb_data referres to customers_bonus
     *
     * The last query combines the data from the 4 subqueries creating one or
     * two rows per distinct date-fleet couple that appears in any of the four
     * groups. Each row contains the date, the fleet and the sum of all four
     * amounts (it considers null values as zero).
     *
     * The result is that the data returned by the query corresponds to the
     * total income for each fleet, divided by date.
     *
     * The params passed specify the format for the date that groups results,
     * the beginning date and the end date.
     *
     * @return string
     */
    protected function sql()
    {
        return "WITH tp AS (
                SELECT to_char(tp.payed_successfully_at, :format) AS date,
                    f.name AS fleet,
                    sum(tp.total_cost) AS amount
                FROM trip_payments tp
                LEFT JOIN trips t ON t.id = tp.trip_id
                LEFT JOIN fleets f ON t.fleet_id = f.id
                WHERE tp.payed_successfully_at >= :start
                AND tp.payed_successfully_at < :end
                GROUP BY date, fleet
                ORDER BY date, fleet ASC
            ), sp AS (
                SELECT to_char(t.datetime, :format) AS date,
                    f.name AS fleet,
                    sum(sp.amount) AS amount
                FROM subscription_payments sp
                LEFT JOIN transactions t ON t.id = sp.transaction_id
                LEFT JOIN fleets f ON f.id = sp.fleet_id
                WHERE t.datetime >= :start
                AND t.datetime < :end
                GROUP BY date, fleet
                ORDER BY date, fleet ASC
            ), ep AS (
                SELECT to_char(t.datetime, :format) AS date,
                    f.name AS fleet,
                    sum(ep.amount) AS amount
                FROM extra_payments ep
                LEFT JOIN transactions t ON t.id = ep.transaction_id
                LEFT JOIN fleets f ON f.id = ep.fleet_id
                WHERE t.datetime >= :start
                AND t.datetime < :end
                GROUP BY date, fleet
                ORDER BY date, fleet ASC
            ), bpp AS (
                SELECT to_char(t.datetime, :format) AS date,
                    f.name AS fleet,
                    sum(bpp.amount) AS amount
                FROM bonus_package_payments bpp
                LEFT JOIN transactions t ON t.id = bpp.transaction_id
                LEFT JOIN fleets f ON f.id = bpp.fleet_id
                WHERE t.datetime >= :start
                AND t.datetime < :end
                GROUP BY date, fleet
                ORDER BY date, fleet ASC
            )
            SELECT COALESCE(tp.date, sp.date, ep.date, bpp.date) AS date,
                COALESCE(tp.fleet, sp.fleet, ep.fleet, bpp.fleet) AS fleet,
                COALESCE(tp.amount, 0) + COALESCE(sp.amount, 0) + COALESCE(ep.amount, 0) + COALESCE(bpp.amount, 0) AS amount
            FROM tp
            FULL JOIN sp ON tp.date = sp.date AND tp.fleet = sp.fleet
            FULL JOIN ep ON tp.date = ep.date AND tp.fleet = ep.fleet
            FULL JOIN bpp ON tp.date = bpp.date AND tp.fleet = bpp.fleet
            ORDER BY date ASC, fleet DESC";
    }

    /**
     * @return array
     */
    protected function scalarResults()
    {
        return [
            ['column_name' => 'date', 'alias' => 'date', 'type' => 'string'],
            ['column_name' => 'fleet', 'alias' => 'fleet', 'type' => 'string'],
            ['column_name' => 'amount', 'alias' => 'amount', 'type' => 'integer']
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
