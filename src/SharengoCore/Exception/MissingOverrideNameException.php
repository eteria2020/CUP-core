<?php

namespace SharengoCore\Exception;

class MissingOverrideNameException extends \InvalidArgumentException
{
    protected $message = 'Errore nei parametri. nameWithPath e overrideName non compatibili';
}
