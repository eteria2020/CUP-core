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
            LEFT JOIN invoices i ON i.id = cb.invoice_id
            LEFT JOIN fleets f ON f.id = i.fleet_id
            LEFT JOIN customers_bonus_packages cbp ON cbp.id = cb.package_id
            WHERE t.datetime >= :start
            AND t.datetime < :end
            GROUP BY cb_date, cb_fleet
            ORDER BY cb_date, cb_fleet ASC
        )
        SELECT tp_data.tp_date AS date,
            tp_data.tp_fleet AS fleet,
            (tp_data.tp_amount + sp_data.sp_amount) AS amount
        FROM tp_data
        LEFT JOIN sp_data ON tp_data.tp_date = sp_data.sp_date AND tp_data.tp_fleet = sp_data.sp_fleet
        LEFT JOIN ep_data ON tp_data.tp_date = ep_data.ep_date AND tp_data.tp_fleet = ep_data.ep_fleet
        LEFT JOIN cb_data ON tp_data.tp_date = cb_data.cb_date AND tp_data.tp_fleet = cb_data.cb_fleet";
    }

    protected function scalarResults()
    {
        return [
            ['column_name' => 'date', 'alias' => 'date', 'type' => 'string'],
            ['column_name' => 'fleet', 'alias' => 'fleet', 'type' => 'string'],
            ['column_name' => 'amount', 'alias' => 'amount', 'type' => 'integer']
        ];
    }

    protected function params()
    {
        return $this->params;
    }
}
