<?php

namespace SharengoCore\Entity;

use Doctrine\ORM\Mapping as ORM;
use SharengoCore\Utils\Interval;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;

/**
 * ServerScripts
 *
 * @ORM\Table(name="server_scripts")
 * @ORM\Entity(repositoryClass="SharengoCore\Entity\Repository\ServerScriptsRepository")
 */
class ServerScripts
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="server_scripts_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="start_ts", type="datetimetz", nullable=false)
     */
    private $startTs;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="end_ts", type="datetimetz", nullable=true)
     */
    private $endTs;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="text", nullable=true)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="full_path", type="text", nullable=true)
     */
    private $fullPath;

    /**
     * @var string
     *
     * @ORM\Column(name="param", type="string", nullable=true)
     */
    private $param;

    /**
     * @var string
     *
     * @ORM\Column(name="error", type="text", nullable=true)
     */
    private $error;

    /**
     * @var string
     *
     * @ORM\Column(name="info_script", type="string", nullable=true)
     */
    private $infoScript;

    /**
     * @var string
     *
     * @ORM\Column(name="note", type="text", nullable=true)
     */
    private $note;


    function getId() {
        return $this->id;
    }

    function getStartTs() {
        return $this->startTs;
    }

    function getEndTs() {
        return $this->endTs;
    }

    function getName() {
        return $this->name;
    }

    function getFullPath() {
        return $this->fullPath;
    }

    function getParam() {
        return $this->param;
    }

    function getError() {
        return $this->error;
    }

    function getInfoScript() {
        return $this->infoScript;
    }

    function getNote() {
        return $this->note;
    }

    function setId($id) {
        $this->id = $id;
    }

    function setStartTs(\DateTime $startTs) {
        $this->startTs = $startTs;
    }

    function setEndTs(\DateTime $endTs) {
        $this->endTs = $endTs;
    }

    function setName($name) {
        $this->name = $name;
    }

    function setFullPath($fullPath) {
        $this->fullPath = $fullPath;
    }

    function setParam($param) {
        $this->param = $param;
    }

    function setError($error) {
        $this->error = $error;
    }

    function setInfoScript($infoScript) {
        $this->infoScript = $infoScript;
    }

    function setNote($note) {
        $this->note = $note;
    }
    
    
}

