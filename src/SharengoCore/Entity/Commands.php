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
     * @ORM\Column(name="payload", type="text", nullable=true)
     */
    private $payload;

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return  string
     */
    public function getCarPlate()
    {
        return $this->carPlate;
    }

    /**
     * @param string
     */
    public function setCarPlate($carPlate)
    {
        $this->carPlate = $carPlate;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getQueued()
    {
        return $this->queued;
    }

    /**
     * @param \DateTime
     */
    public function setQueued($queued)
    {
        $this->queued = $queued;
        return $this;
    }

    /**
     * @return boolean
     */
    public function getToSend()
    {
        return $this->toSend;
    }

    /**
     * @param boolean
     */
    public function setToSend($toSend)
    {
        $this->toSend = $toSend;
        return $this;
    }

    /**
     * @return string
     */
    public function getCommand()
    {
        return $this->command;
    }

    /**
     * @param string
     */
    public function setCommand($command)
    {
        $this->command = $command;
        return $this;
    }

    /**
     * @return integer
     */
    public function getTxtarg1()
    {
        return $this->txtarg1;
    }

    /**
     * @param integer
     */
    public function setTxtarg1($txtarg1)
    {
        $this->txtarg1 = $txtarg1;
        return $this;
    }

}

