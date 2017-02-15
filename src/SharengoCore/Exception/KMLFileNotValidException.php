<?php

namespace SharengoCore\Exception;

class KMLFileNotValidException extends \DomainException
{
    protected $message = 'The file provided is not a valid KML file.';
}
