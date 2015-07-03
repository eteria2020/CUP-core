<?php

namespace SharengoCore\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ReservationsArchive
 *
 * @ORM\Table(name="reservations_archive", indexes={@ORM\Index(name="IDX_4DA2399395C3F3", columns={"customer_id"}), @ORM\Index(name="IDX_4DA239AE35528C", columns={"car_plate"})})
 * @ORM\Entity(repositoryClass="SharengoCore\Entity\Repository\ReservationsArchiveRepository")
 */
class ReservationsArchive
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="reservations_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="ts", type="datetimetz", nullable=false)
     */
    private $ts;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="beginning_ts", type="datetimetz", nullable=false)
     */
    private $beginningTs;

    /**
     * @var boolean
     *
     * @ORM\Column(name="active", type="boolean", nullable=false)
     */
    private $active = true;

    /**
     * @var string
     *
     * @ORM\Column(name="cards", type="text", nullable=true)
     */
    private $cards;

    /**
     * @var integer
     *
     * @ORM\Column(name="length", type="integer", nullable=false)
     */
    private $length;

    /**
     * @var boolean
     *
     * @ORM\Column(name="to_send", type="boolean", nullable=true)
     */
    private $toSend = true;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="sent_ts", type="datetimetz", nullable=false)
     */
    private $sentTs;

    /**
     * @var \SharengoCore\Entity\Customers
     *
     * @ORM\ManyToOne(targetEntity="SharengoCore\Entity\Customers")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="customer_id", referencedColumnName="id")
     * })
     */
    private $customer;

    /**
     * @var \SharengoCore\Entity\Cars
     *
     * @ORM\ManyToOne(targetEntity="SharengoCore\Entity\Cars")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="car_plate", referencedColumnName="plate")
     * })
     */
    private $car;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="consumed_ts", type="datetimetz")
     */
    private $consumedTs;

    /**
     * @var string
     *
     * @ORM\Column(name="reason", type="string", nullable=false)
     */
    private $reason;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="archived_ts", type="datetimetz", nullable=false)
     */
    private $archivedTs;



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
     * Set ts
     *
     * @param \DateTime $ts
     *
     * @return Reservations
     */
    public function setTs($ts)
    {
        $this->ts = $ts;

        return $this;
    }

    /**
     * Get ts
     *
     * @return \DateTime
     */
    public function getTs()
    {
        return $this->ts;
    }

    /**
     * Set beginningTs
     *
     * @param \DateTime $beginningTs
     *
     * @return Reservations
     */
    public function setBeginningTs($beginningTs)
    {
        $this->beginningTs = $beginningTs;

        return $this;
    }

    /**
     * Get beginningTs
     *
     * @return \DateTime
     */
    public function getBeginningTs()
    {
        return $this->beginningTs;
    }

    /**
     * Set active
     *
     * @param boolean $active
     *
     * @return Reservations
     */
    public function setActive($active)
    {
        $this->active = $active;

        return $this;
    }

    /**
     * Get active
     *
     * @return boolean
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * Set cards
     *
     * @param string $cards
     *
     * @return Reservations
     */
    public function setCards($cards)
    {
        $this->cards = $cards;

        return $this;
    }

    /**
     * Get cards
     *
     * @return string
     */
    public function getCards()
    {
        return $this->cards;
    }

    /**
     * Set length
     *
     * @param integer $length
     *
     * @return Reservations
     */
    public function setLength($length)
    {
        $this->length = $length;

        return $this;
    }

    /**
     * Get length
     *
     * @return integer
     */
    public function getLength()
    {
        return $this->length;
    }

    /**
     * Set toSend
     *
     * @param boolean $toSend
     *
     * @return Reservations
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
     * Set sentTs
     *
     * @param \DateTime $sentTs
     *
     * @return Reservations
     */
    public function setSentTs($sentTs)
    {
        $this->sentTs = $sentTs;

        return $this;
    }

    /**
     * Get sentTs
     *
     * @return \DateTime
     */
    public function getSentTs()
    {
        return $this->sentTs;
    }

    /**
     * Set customer
     *
     * @param \SharengoCore\Entity\Customers $customer
     *
     * @return Reservations
     */
    public function setCustomer(\SharengoCore\Entity\Customers $customer = null)
    {
        $this->customer = $customer;

        return $this;
    }

    /**
     * Get customer
     *
     * @return \SharengoCore\Entity\Customers
     */
    public function getCustomer()
    {
        return $this->customer;
    }

    /**
     * Set car
     *
     * @param \SharengoCore\Entity\Cars $carPlate
     *
     * @return Reservations
     */
    public function setCar(\SharengoCore\Entity\Cars $car = null)
    {
        $this->car = $car;

        return $this;
    }

    /**
     * Get car
     *
     * @return \SharengoCore\Entity\Cars
     */
    public function getCar()
    {
        return $this->car;
    }

    /**
     * Get consumedTs
     *
     * @return \DateTime
     */
    public function getConsumedTs()
    {
        return $this->consumedTs;
    }

    /**
     * Set consumedTs
     *
     * @param \DateTime $consumedTs
     * @return Reservations
     */
    public function setConsumedTs($consumedTs)
    {
        $this->consumedTs = $consumedTs;
        return $this;
    }

    /**
     * Get reason
     *
     * @return string
     */
    public function getReason()
    {
        return $this->reason;
    }

    /**
     * Set reason
     *
     * @param string $reason
     * @return Reservations
     */
    public function setReason($reason)
    {
        $this->reason = $reason;
        return $this;
    }

    /**
     * Get archivedTs
     *
     * @return \DateTime
     */
    public function getArchivedTs()
    {
        return $this->archivedTs;
    }

    /**
     * Set archivedTs
     *
     * @param \DateTime $archivedTs
     * @return Reservations
     */
    public function setArchivedTs($archivedTs)
    {
        $this->archivedTs = $archivedTs;
        return $this;
    }

}
