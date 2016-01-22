<?php

namespace SharengoCore\Exception;

class CartasiCsvAnomalyAlreadyResolvedException extends \Exception
{
    protected $message = 'Anomaly has already been marked as resolved.';
}
