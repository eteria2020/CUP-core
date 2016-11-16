<?php

namespace SharengoCore\Service;

use Doctrine\DBal\Connection;

class PaymentScriptRunsService
{
    /**
     * @var Connection
     */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function scriptStarted()
    {
        $statement = $this->connection->executeQuery(
            'INSERT INTO payment_script_runs (start_ts) VALUES (:start) RETURNING id',
            ['start' => date_create()]
        );

        return $statement->fetchColumn();
    }

    public function scriptEnded($id)
    {
        $statement = $this->connection->update(
            'UPDATE payment_script_runs SET end_ts = :end WHERE id = :id',
            [
                'end' => date_create(),
                'id' => $id
            ]
        );
    }

    public function isScriptRunning()
    {
        $lastRun = $this->connection->fetchAll(
            'select start_ts, end_ts FROM payment_script_runs ORDER BY id DESC LIMIT 1'
        );

        if (empty($lastRun)) {
            return false;
        }

        $lastRun = $lastRun[0];

        return $lastRun['end_ts'] === null && ($lastRun['start_ts'] >= date_create('-4 hours'));
    }
}
