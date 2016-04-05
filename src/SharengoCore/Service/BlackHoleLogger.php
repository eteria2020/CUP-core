<?php

namespace SharengoCore\Service;

/**
 * Stub logger to be used where a logger is needed but nothing needs to be
 * actually logged
 */
class BlackHoleLogger implements LoggerInterface
{
    public function log($message)
    {
        return;
    }
}
