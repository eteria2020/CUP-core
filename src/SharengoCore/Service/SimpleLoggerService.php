<?php

namespace SharengoCore\Service;

class SimpleLoggerService
{
    const OUTPUT_OFF = 0;

    const OUTPUT_ON = 1;

    const OUTPUT_PROD = 2;

    const OUTPUT_DEV = 3;

    const TYPE_CONSOLE = -1;

    const ENV_PROD = 'production';

    const ENV_DEV = 'development';

    /**
     * @var string   defines what kind of output to use
     */
    private $outputType;

    /**
     * @var string   defines the environment (development | production)
     */
    private $environment;

    /**
     * @param [string] $simpleLoggerConfig
     */
    public function __construct($simpleLoggerConfig)
    {
        $this->environment = $simpleLoggerConfig['environment'];
    }

    /**
     * @param integer $outputEnvironment
     */
    public function setOutputEnvironment($outputEnvironment = self::OUTPUT_OFF)
    {
        $this->outputEnvironment = $outputEnvironment;
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
        if ($this->outputEnvironment == self::OUTPUT_ON ||
            ($this->outputEnvironment == self::OUTPUT_DEV && $this->environment == self::ENV_DEV) ||
            ($this->outputEnvironment == self::OUTPUT_PROD && $this->environment == self::ENV_PROD)
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
