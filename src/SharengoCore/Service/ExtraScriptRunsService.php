<?php

namespace SharengoCore\Service;

use Doctrine\DBal\Connection;

class ExtraScriptRunsService
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
            'INSERT INTO extra_script_runs (start_ts) VALUES (:start) RETURNING id',
            ['start' => date_create()->format('Y-m-d H:i:s')]
        );

        return $statement->fetchColumn();
    }

    public function scriptEnded($id)
    {
        $this->connection->update(
            'extra_script_runs',
            ['end_ts' => date_create()->format('Y-m-d H:i:s')],
            ['id' => $id]
        );
    }

    public function isScriptRunning()
    {
        $lastRun = $this->connection->fetchAll(
            'select start_ts, end_ts FROM extra_script_runs ORDER BY id DESC LIMIT 1'
        );

        if (empty($lastRun)) {
            return false;
        }

        $lastRun = $lastRun[0];

        return $lastRun['end_ts'] === null; //&& (date_create_from_format('Y-m-d H:i:s', $lastRun['start_ts']) >= date_create('-4 hours'));
    }

    /** Method returns the status of the script pay invoice
     *
     * @return boolean
     */
    public function isRunning()
    {
        $lastRun = $this->connection->fetchAll(
            "select * from extra_script_runs where now()-start_ts <'24:00:00' and end_ts is null"
        );

        if (empty($lastRun)) {
            return false;
        } else {
            return true;
        }
    }
}
