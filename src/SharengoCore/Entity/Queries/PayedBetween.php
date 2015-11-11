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
        return "WITH tp_data AS (
                SELECT to_char(tp.payed_successfully_at, :format) AS tp_date,
                    f.name AS tp_fleet,
                    sum(tp.total_cost) AS tp_amount
                FROM trip_payments tp
                LEFT JOIN trips t ON t.id = tp.trip_id
                LEFT JOIN fleets f ON t.fleet_id = f.id
                WHERE tp.payed_successfully_at >= :start
                AND tp.payed_successfully_at < :end
                GROUP BY tp_date, tp_fleet
                ORDER BY tp_date, tp_fleet ASC
            ), sp_data AS (
                SELECT to_char(t.datetime, :format) AS sp_date,
                    f.name AS sp_fleet,
                    sum(sp.amount) AS sp_amount
                FROM subscription_payments sp
                LEFT JOIN transactions t ON t.id = sp.transaction_id
                LEFT JOIN fleets f ON f.id = sp.fleet_id
                WHERE t.datetime >= :start
                AND t.datetime < :end
                GROUP BY sp_date, sp_fleet
                ORDER BY sp_date, sp_fleet ASC
            ), ep_data AS (
                SELECT to_char(t.datetime, :format) AS ep_date,
                    f.name AS ep_fleet,
                    sum(ep.amount) AS ep_amount
                FROM extra_payments ep
                LEFT JOIN transactions t ON t.id = ep.transaction_id
                LEFT JOIN fleets f ON f.id = ep.fleet_id
                WHERE t.datetime >= :start
                AND t.datetime < :end
                GROUP BY ep_date, ep_fleet
                ORDER BY ep_date, ep_fleet ASC
            ), cb_data AS (
                SELECT to_char(t.datetime, :format) AS cb_date,
                    f.name AS cb_fleet,
                    sum(cbp.cost) AS cb_amount
                FROM customers_bonus cb
                LEFT JOIN transactions t ON t.id = cb.transaction_id
                LEFT JOIN fleets f ON f.id = cb.payment_fleet_id
                LEFT JOIN customers_bonus_packages cbp ON cbp.id = cb.package_id
                WHERE t.datetime >= :start
                AND t.datetime < :end
                GROUP BY cb_date, cb_fleet
                ORDER BY cb_date, cb_fleet ASC
            )
            SELECT COALESCE(tp_data.tp_date, sp_data.sp_date, ep_data.ep_date, cb_data.cb_date) AS date,
                COALESCE(tp_data.tp_fleet, sp_data.sp_fleet, ep_data.ep_fleet, cb_data.cb_fleet) AS fleet,
                COALESCE(tp_data.tp_amount, 0) + COALESCE(sp_data.sp_amount, 0) + COALESCE(ep_data.ep_amount, 0) + COALESCE(cb_data.cb_amount, 0) AS amount
            FROM tp_data
            FULL JOIN sp_data ON tp_data.tp_date = sp_data.sp_date AND tp_data.tp_fleet = sp_data.sp_fleet
            FULL JOIN ep_data ON tp_data.tp_date = ep_data.ep_date AND tp_data.tp_fleet = ep_data.ep_fleet
            FULL JOIN cb_data ON tp_data.tp_date = cb_data.cb_date AND tp_data.tp_fleet = cb_data.cb_fleet";
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
