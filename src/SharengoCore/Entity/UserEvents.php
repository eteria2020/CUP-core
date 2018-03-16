<?php

namespace SharengoCore\Entity;

use Doctrine\ORM\Mapping as ORM;
use SharengoCore\Entity\Webuser;

/**
 * UserEvents
 *
 * @ORM\Table(name="user_events", indexes={@ORM\Index(name="idx_d96cf1ff49279951", columns={"webuser_id"})})
 * @ORM\Entity
 */
class UserEvents {

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="user_events_id_seq", allocationSize=1, initialValue=1)
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
     * @var \Webuser
     *
     * @ORM\ManyToOne(targetEntity="Webuser")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="webuser_id", referencedColumnName="id")
     * })
     */
    private $webuser;

    /**
     * 
     * @param Webuser $webUser
     * @param type $topic
     * @param array $detail
     */
    public function __construct(Webuser $webUser, $topic, array $detail) {
        $this->insertTs = date_create();
        $this->webuser = $webUser;
        $this->topic = $topic;
        $this->details = json_encode($detail);
    }

}
