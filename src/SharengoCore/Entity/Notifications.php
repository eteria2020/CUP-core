<?php

namespace SharengoCore\Entity;

// Internals
use SharengoCore\Entity\NotificationsProtocols;
use SharengoCore\Entity\NotificationsCategories;
use SharengoCore\Entity\Webuser;
// Externals
use Doctrine\ORM\Mapping as ORM;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use DateTime;

/**
 * Notifications
 *
 * @ORM\Table(name="messages_outbox")
 * @ORM\Entity(repositoryClass="SharengoCore\Entity\Repository\NotificationsRepository")
 */
class Notifications
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="messages_outbox_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var NotificationsProtocols
     *
     * @ORM\ManyToOne(targetEntity="NotificationsProtocols")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="transport", referencedColumnName="name", nullable=true)
     * })
     */
    private $protocol;

    /**
     * @var string
     *
     * @ORM\Column(name="destination", type="text", nullable=true)
     */
    private $destination;

    /**
     * @var NotificationsCategories
     *
     * @ORM\ManyToOne(targetEntity="NotificationsCategories")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="type", referencedColumnName="name", nullable=true)
     * })
     */
    private $category;

    /**
     * @var string
     *
     * @ORM\Column(name="subject", type="text", nullable=true)
     */
    private $subject;

    /**
     * @var string
     *
     * @ORM\Column(name="text", type="text", nullable=true)
     */
    private $text;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="submitted", type="datetimetz", nullable=true)
     */
    private $submitDate;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="sent", type="datetimetz", nullable=true)
     */
    private $sentDate;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="acknowledged", type="datetimetz", nullable=true)
     */
    private $acknowledgeDate;

    /**
     * @var array
     *
     * @ORM\Column(name="meta", type="json_array", nullable=true)
     */
    private $meta;

    /**
     * @var array
     *
     * @ORM\Column(name="sent_meta", type="json_array", nullable=true)
     */
    private $sentMeta;
    
    /**
     * @var integer
     * 
     * @ORM\ManyToOne(targetEntity="Webuser")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="webuser_id", referencedColumnName="id", nullable=true)
     * })
     */
    private $webuser;


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
     * Get destination
     *
     * @return string
     */
    public function getDestination()
    {
        return $this->destination;
    }

    /**
     * Get subject
     *
     * @return string
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * Get submitDate
     *
     * @return DateTime
     */
    public function getSubmitDate()
    {
        return $this->submitDate;
    }

    /**
     * Get sentDate
     *
     * @return DateTime
     */
    public function getSentDate()
    {
        return $this->sentDate;
    }

    /**
     * Get acknowledgeDate
     *
     * @return DateTime
     */
    public function getAcknowledgeDate()
    {
        return $this->acknowledgeDate;
    }
    
    /**
     * Get webuser
     *
     * @return string 
     */
    public function getWebuser()
    {
        
        if ($this->webuser instanceof Webuser) {
            return $this->webuser->getDisplayName();
        }else{
            return null;
        }
        
    }

    /**
     * Get categoryName
     *
     * @return string (NotificationsCategories->name) | null
     */
    public function getCategoryName()
    {
        if ($this->category instanceof NotificationsCategories) {
            return $this->category->getName();
        }
        return null;
    }

    /**
     * Get protocolName slug
     *
     * @return string (NotificationsProtocols->name slug) | null
     */
    public function getCategoryNameSlug()
    {
        if ($this->category instanceof NotificationsCategories) {
            return $this->category->getNameSlug();
        }
        return null;
    }

    /**
     * Get protocolName slug
     *
     * @return string | null
     */
    public function getCategoryDefaultProtocolName()
    {
        if ($this->category instanceof NotificationsCategories) {
            return $this->category->getDefaultProtocolName();
        }
        return null;
    }

    /**
     * Get categoryName
     *
     * @return string (NotificationsProtocols->name) | null
     */
    public function getProtocolName()
    {
        if ($this->protocol instanceof NotificationsProtocols) {
            return $this->protocol->getName();
        }
        return null;
    }

    /**
     * Get meta
     *
     * @return array
     */
    public function getMeta()
    {
        return $this->meta;
    }

    /**
     * Set acknowledgeDate
     *
     * @param DateTime $acknowledgeDate
     * @return Notifications
     */
    public function setAcknowledgeDate(DateTime $acknowledgeDate)
    {
        $this->acknowledgeDate = $acknowledgeDate;

        return $this;
    }
    
    /**
     * Set webuserId
     *
     * @param Webuser $webuser
     * @return Notifications
     */
    public function setWebuser(Webuser $webuser)
    {
        $this->webuser = $webuser;

        return $this;
    }
}
