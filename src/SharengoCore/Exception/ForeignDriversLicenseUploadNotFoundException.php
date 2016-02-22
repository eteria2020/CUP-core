<?php

namespace SharengoCore\Exception;

class ForeignDriversLicenseUploadNotFoundException extends \UnexpectedValueException
{
    protected $message = 'Unable to retrieve the foreign drivers license uploaded file';
}
