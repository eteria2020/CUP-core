<?php

namespace SharengoCore\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Commands
 *
 * @ORM\Table(name="commands")
 * @ORM\Entity(repositoryClass="SharengoCore\Entity\Repository\CommandsRepository")
 */
class Commands
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="bigint", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="commands_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="car_plate", type="text", nullable=true)
     */
    private $carPlate;

    /**
     * @var string
     *
     * @ORM\Column(name="command", type="text", nullable=true)
     */
    private $command;

    /**
     * @var integer
     *
     * @ORM\Column(name="intarg1", type="integer", nullable=true)
     */
    private $intarg1 = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="intarg2", type="integer", nullable=true)
     */
    private $intarg2 = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="txtarg1", type="text", nullable=true)
     */
    private $txtarg1;

    /**
     * @var string
     *
     * @ORM\Column(name="txtarg2", type="text", nullable=true)
     */
    private $txtarg2;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="queued", type="datetimetz", nullable=true)
     */
    private $queued;

    /**
     * @var boolean
     *
     * @ORM\Column(name="to_send", type="boolean", nullable=true)
     */
    private $toSend = false;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="received", type="datetimetz", nullable=true)
     */
    private $received;

    /**
     * @var integer
     *
     * @ORM\Column(name="ttl", type="integer", nullable=true)
     */
    private $ttl = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="payload", type="string", nullable=true)
     */
    private $payload;

    /**
     * @var array
     */
    private static $codes = [
        0 => ['label' => 'Ricarica completata', 'command' => 'END_CHARGE', 'params' => [], 'ttl' => 0],
        1 => ['label' => 'Abilita motore', 'command' => 'SET_ENGINE', 'params' => ['intarg1' => 1], 'ttl' => 180],
        2 => ['label' => 'Disabilita motore', 'command' => 'SET_ENGINE', 'params' => ['intarg1' => 0], 'ttl' => 180],
        3 => ['label' => 'Apri portiere', 'command' => 'SET_DOORS', 'params' => ['intarg1' => 1], 'ttl' => 180],
        4 => ['label' => 'Chiudi portiere', 'command' => 'SET_DOORS', 'params' => ['intarg1' => 0], 'ttl' => 180],
        5 => ['label' => 'Scarica whitelist', 'command' => 'WLUPDATE', 'params' => [], 'ttl' => 180],
        6 => ['label' => 'Cancella e riscarica whitelist', 'command' => 'WLCLEAN', 'params' => [], 'ttl' => 180],
        7 => ['label' => 'Rispedisci corse', 'command' => 'RESEND_TRIP', 'params' => [], 'ttl' => 180],
        8 => ['label' => 'Apri finestra di servizio', 'command' => 'OPEN_SERVICE', 'params' => [], 'ttl' => 60],
        9 => ['label' => 'Chiudi ultima corsa aperta', 'command' => 'CLOSE_TRIP', 'params' => [], 'ttl' => 60],
        10 => ['label' => 'Usa coordinate GPRS', 'command' => 'SET_CONFIG', 'params' => ['txtarg1' => '{UseExternalGPS : true}'], 'ttl' => 60],
        11 => ['label' => 'Usa coordinate ANDROID', 'command' => 'SET_CONFIG', 'params' => ['txtarg1' => '{UseExternalGPS : false}'], 'ttl' => 60],
    ];

    /**
     * @param Cars $car
     * @param integer $commandIndex
     * @param Webuser|null $webuser
     * @return Commands
     */
    public static function createCommand(Cars $car, $commandIndex, Webuser $webuser = null) {
        if (!array_key_exists($commandIndex, self::$codes)) {
            throw new \InvalidArgumentException('Command not found');
        }

        $commandData = self::$codes[$commandIndex];

        $command = new Commands();
        $command->setCarPlate($car->getPlate());
        $command->setCommand($commandData['command']);

        foreach($commandData['params'] as $param => $value) {
            $methodName = 'set' . ucfirst($param);
            $command->$methodName($value);
        }

        if ($command->getCommand() == 'END_CHARGE') {
            $command->setTxtarg2($webuser->getId());
        }

        $command->setQueued(new \DateTime());
        $command->setToSend(true);
        $command->setTtl($commandData['ttl']);

        return $command;
    }

    /**
     * @return array
     */
    public static function getCommandCodes() {
        $list = [];

        foreach(self::$codes as $key => $command) {
            $list[$key] = $command['label'];
        }

        return $list;
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set carPlate
     *
     * @param string $carPlate
     *
     * @return Commands
     */
    public function setCarPlate($carPlate)
    {
        $this->carPlate = $carPlate;

        return $this;
    }

    /**
     * Get carPlate
     *
     * @return string
     */
    public function getCarPlate()
    {
        return $this->carPlate;
    }

    /**
     * Set command
     *
     * @param string $command
     *
     * @return Commands
     */
    public function setCommand($command)
    {
        $this->command = $command;

        return $this;
    }

    /**
     * Get command
     *
     * @return string
     */
    public function getCommand()
    {
        return $this->command;
    }

    /**
     * Set intarg1
     *
     * @param integer $intarg1
     *
     * @return Commands
     */
    public function setIntarg1($intarg1)
    {
        $this->intarg1 = $intarg1;

        return $this;
    }

    /**
     * Get intarg1
     *
     * @return integer
     */
    public function getIntarg1()
    {
        return $this->intarg1;
    }

    /**
     * Set intarg2
     *
     * @param integer $intarg2
     *
     * @return Commands
     */
    public function setIntarg2($intarg2)
    {
        $this->intarg2 = $intarg2;

        return $this;
    }

    /**
     * Get intarg2
     *
     * @return integer
     */
    public function getIntarg2()
    {
        return $this->intarg2;
    }

    /**
     * Set txtarg1
     *
     * @param string $txtarg1
     *
     * @return Commands
     */
    public function setTxtarg1($txtarg1)
    {
        $this->txtarg1 = $txtarg1;

        return $this;
    }

    /**
     * Get txtarg1
     *
     * @return string
     */
    public function getTxtarg1()
    {
        return $this->txtarg1;
    }

    /**
     * Set txtarg2
     *
     * @param string $txtarg2
     *
     * @return Commands
     */
    public function setTxtarg2($txtarg2)
    {
        $this->txtarg2 = $txtarg2;

        return $this;
    }

    /**
     * Get txtarg2
     *
     * @return string
     */
    public function getTxtarg2()
    {
        return $this->txtarg2;
    }

    /**
     * Set queued
     *
     * @param \DateTime $queued
     *
     * @return Commands
     */
    public function setQueued($queued)
    {
        $this->queued = $queued;

        return $this;
    }

    /**
     * Get queued
     *
     * @return \DateTime
     */
    public function getQueued()
    {
        return $this->queued;
    }

    /**
     * Set toSend
     *
     * @param boolean $toSend
     *
     * @return Commands
     */
    public function setToSend($toSend)
    {
        $this->toSend = $toSend;

        return $this;
    }

    /**
     * Get toSend
     *
     * @return boolean
     */
    public function getToSend()
    {
        return $this->toSend;
    }

    /**
     * Set received
     *
     * @param \DateTime $received
     *
     * @return Commands
     */
    public function setReceived($received)
    {
        $this->received = $received;

        return $this;
    }

    /**
     * Get received
     *
     * @return \DateTime
     */
    public function getReceived()
    {
        return $this->received;
    }

    /**
     * Set ttl
     *
     * @param integer $ttl
     *
     * @return Commands
     */
    public function setTtl($ttl)
    {
        $this->ttl = $ttl;

        return $this;
    }

    /**
     * Get ttl
     *
     * @return integer
     */
    public function getTtl()
    {
        return $this->ttl;
    }

    /**
     * Set payload
     *
     * @param string $payload
     *
     * @return Commands
     */
    public function setPayload($payload)
    {
        $this->payload = $payload;

        return $this;
    }

    /**
     * Get payload
     *
     * @return string
     */
    public function getPayload()
    {
        return $this->payload;
    }
}
