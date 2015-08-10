<?php

namespace SharengoCore\Service;

class SimpleLoggerService
{
    const OUTPUT_OFF = 0;

    const OUTPUT_ON = 1;

    const OUTPUT_PROD = 2;

    const OUTPUT_DEV = 3;

    const TYPE_CONSOLE = 'console';

    const ENV_PROD = 'production';

    const ENV_DEV = 'development';

    /**
     * @var string   defines what kind of output to use
     */
    private $outputType;

    /**
     * @var string   defines the enviornment (development | production)
     */
    private $enviornment;

    /**
     * @param [string] $simpleLoggerConfig
     */
    public function __construct($simpleLoggerConfig)
    {
        $this->enviornment = $simpleLoggerConfig['enviornment'];
    }

    /**
     * @param integer $outputEnviornment
     */
    public function setOutputEnviornment($outputEnviornment = self::OUTPUT_OFF)
    {
        $this->outputEnviornment = $outputEnviornment;
    }

    /**
     * @param string $outputType
     */
    public function setOutputType($outputType)
    {
        $this->outputType = $outputType;
    }

    /**
     * @param string $message
     */
    public function log($message = "")
    {
        if ($this->outputEnviornment == self::OUTPUT_ON ||
            ($this->outputEnviornment == self::OUTPUT_DEV && $this->enviornment == self::ENV_DEV) ||
            ($this->outputEnviornment == self::OUTPUT_PROD && $this->enviornment == self::ENV_PROD)
        ) {
            $this->writeMessage($message);
        }
    }

    /**
     * @param string $message
     */
    private function writeMessage($message)
    {
        if ($this->outputType == self::TYPE_CONSOLE) {
            $this->writeToConsole($message);
        }
    }

    /**
     * @param string $message
     */
    private function writeToConsole($message)
    {
        fwrite(STDOUT, $message);
    }

}
