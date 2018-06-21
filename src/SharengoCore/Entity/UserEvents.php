<?php

namespace SharengoCore\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * UserEvents
 *
 * @ORM\Table(name="user_events")
 * @ORM\Entity(repositoryClass="SharengoCore\Entity\Repository\UserEventsRepository")
 */
class UserEvents
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="userevents_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="insert_ts", type="datetime", nullable=false)
     */
    private $insertTs;

    /**
     * @var string
     *
     * @ORM\Column(name="topic", type="string", length=100, nullable=false)
     */
    private $topic;

    /**
     * @var string
     *
     * @ORM\Column(name="details", type="string", nullable=false)
     */
    private $details;

    /**
     * @var Webuser
     *
     * @ORM\ManyToOne(targetEntity="Webuser")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="webuser_id", referencedColumnName="id")
     * })
     */
    private $webuser;

    
    /**
     * @param Webuser $webuser
     * @param String $topic
     * @param json $details
     */
    public function __construct(
        Webuser $webuser,
        $topic,
        $details
    ) {
        $this->webuser = $webuser;
        $this->insertTs = new \DateTime();
        $this->topic = $topic;
        $this->details = $details;
    }

}

