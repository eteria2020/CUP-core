<?php

namespace SharengoCore\Entity\Queries;

use Doctrine\ORM\EntityManagerInterface;
use SharengoCore\Entity\Invoices;

class PayedFleetYearBetween extends NativeQuery
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
    public function __construct(EntityManagerInterface $em, $year, $id_fleet, $format)
    {
        parent::__construct($em);
        $this->params = [
            'format' => $format,
            'start' => "$year-01-01 00:00:00",
            'end' => "$year-12-31 23:59:59",
            'id_fleet' => $id_fleet
        ];
    }

    /**
     * This query is divided in one main query and 4 "subqueries".
     * Each of the 4 subqueries collects data for a specific kind of payment.
     * The data is organized in three fields, one for the date, one for the
     * fleet and one for the amount.
     *
     * - tp referres to trip_payments
     * - sp referres to subscription_payments
     * - ep referres to extra_payments
     * - bpp referres to bonus_package_payments
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
                    sum(tp.total_cost) AS amount
                FROM trip_payments tp
                LEFT JOIN trips t ON t.id = tp.trip_id
                WHERE tp.payed_successfully_at >= :start
                AND tp.payed_successfully_at < :end
                AND t.fleet_id = :id_fleet
                GROUP BY date
                ORDER BY date ASC
            ), sp AS (
                SELECT to_char(sp.insert_ts, :format) AS date,
                    sum(sp.amount) AS amount
                FROM subscription_payments sp
                WHERE sp.insert_ts >= :start
                AND sp.insert_ts < :end
                AND sp.fleet_id = :id_fleet
                GROUP BY date
                ORDER BY date ASC
            ), ep AS (
                SELECT to_char(ep.generated_ts, :format) AS date,
                    sum(ep.amount) AS amount
                FROM extra_payments ep
                WHERE ep.generated_ts >= :start
                AND ep.generated_ts < :end
                AND ep.fleet_id = :id_fleet
                GROUP BY date
                ORDER BY date ASC
            ), bpp AS (
                SELECT to_char(bpp.inserted_ts, :format) AS date,
                    sum(bpp.amount) AS amount
                FROM bonus_package_payments bpp
                WHERE bpp.inserted_ts >= :start
                AND bpp.inserted_ts < :end
                AND bpp.fleet_id = :id_fleet
                GROUP BY date
                ORDER BY date ASC
            )
            SELECT date, SUM(tp_amount) as tp_amount,
						 SUM(sp_amount) as sp_amount,
						 SUM(ep_amount) as ep_amount,
						 SUM(bpp_amount) as bpp_amount,
						 SUM(amount) as amount FROM (
                SELECT COALESCE(tp.date, sp.date, ep.date, bpp.date) AS date,
					COALESCE(tp.amount, 0) as tp_amount,
					COALESCE(sp.amount, 0) as sp_amount,
					COALESCE(ep.amount, 0) as ep_amount,
					COALESCE(bpp.amount, 0) as bpp_amount,
                    COALESCE(tp.amount, 0) + COALESCE(sp.amount, 0) + COALESCE(ep.amount, 0) + COALESCE(bpp.amount, 0) AS amount
                FROM tp
                FULL JOIN sp ON tp.date = sp.date
                FULL JOIN ep ON tp.date = ep.date
                FULL JOIN bpp ON tp.date = bpp.date
                ORDER BY date ASC) AS r 
            GROUP BY date";
    }

    /**
     * @return array
     */
    protected function scalarResults()
    {
        return [
            ['column_name' => 'date', 'alias' => 'date', 'type' => 'string'],
            ['column_name' => 'tp_amount', 'alias' => 'tp_amount', 'type' => 'integer'],
            ['column_name' => 'sp_amount', 'alias' => 'sp_amount', 'type' => 'integer'],
            ['column_name' => 'ep_amount', 'alias' => 'ep_amount', 'type' => 'integer'],
            ['column_name' => 'bpp_amount', 'alias' => 'bpp_amount', 'type' => 'integer'],
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
