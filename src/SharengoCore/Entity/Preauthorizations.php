<?php

namespace SharengoCore\Entity;

use Cartasi\Entity\Transactions as Transactions;
use SharengoCore\Entity\Customers as Customers;
use SharengoCore\Entity\Trips as Trips;
use Doctrine\ORM\Mapping as ORM;

/**
 * Preauthorizations
 *
 * @ORM\Table(name="preauthorizations", indexes={@ORM\Index(name="IDX_7E5E76729395C3F3", columns={"customer_id"}), @ORM\Index(name="IDX_7E5E7672A5BC2E0E", columns={"trip_id"}), @ORM\Index(name="IDX_7E5E76722FC0CB0F", columns={"transaction_id"})})
 * @ORM\Entity(repositoryClass="SharengoCore\Entity\Repository\PreauthorizationsRepository")
 */
class Preauthorizations
{
    const STATUS_TO_BE_PAYED = 'to_be_payed';
    const STATUS_REFUND = 'to_be_refund';
    const STATUS_WRONG = 'wrong';
    const STATUS_COMPLETED = 'done';

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="preauthorizations_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime", nullable=true)
     */
    private $createdAt;

    /**
     * @var string
     *
     * @ORM\Column(name="status", type="text", nullable=true)
     */
    private $status;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="status_from", type="datetime", nullable=true)
     */
    private $statusFrom;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="successfully_at", type="datetime", nullable=true)
     */
    private $successfullyAt;

    /**
     * @var Customers
     *
     * @ORM\ManyToOne(targetEntity="Customers")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="customer_id", referencedColumnName="id")
     * })
     */
    private $customer;

    /**
     * @var Trips
     *
     * @ORM\ManyToOne(targetEntity="Trips")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="trip_id", referencedColumnName="id")
     * })
     */
    private $trip;

    /**
     * @var Transactions
     *
     * @ORM\ManyToOne(targetEntity="\Cartasi\Entity\Transactions")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="transaction_id", referencedColumnName="id")
     * })
     */
    private $transaction;

    /**
     * Preauthorizations constructor.
     * @param \DateTime $createdAt
     * @param string $status
     * @param \DateTime $statusFrom
     * @param \DateTime $successfullyAt
     * @param Customers $customer
     * @param Trips $trip
     * @param Transactions $transaction
     */
    public function __construct($status = null, \DateTime $statusFrom = null, \DateTime $successfullyAt = null, Customers $customer, Trips $trip, Transactions $transaction = null)
    {
        $this->createdAt = date_create(date('Y-m-d H:i:s'));
        $this->status = $status;
        $this->statusFrom = $statusFrom;
        $this->successfullyAt = $successfullyAt;
        $this->customer = $customer;
        $this->trip = $trip;
        $this->transaction = $transaction;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTime $createdAt
     * @return $this
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param string $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getStatusFrom()
    {
        return $this->statusFrom;
    }

    /**
     * @param \DateTime $statusFrom
     */
    public function setStatusFrom($statusFrom)
    {
        $this->statusFrom = $statusFrom;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getSuccessfullyAt()
    {
        return $this->successfullyAt;
    }

    /**
     * @param \DateTime $successfullyAt
     */
    public function setSuccessfullyAt($successfullyAt)
    {
        $this->successfullyAt = $successfullyAt;
        return $this;
    }

    /**
     * @return Customers
     */
    public function getCustomer()
    {
        return $this->customer;
    }

    /**
     * @param Customers $customer
     */
    public function setCustomer($customer)
    {
        $this->customer = $customer;
        return $this;
    }

    /**
     * @return Trips
     */
    public function getTrip()
    {
        return $this->trip;
    }

    /**
     * @param Trips $trip
     */
    public function setTrip($trip)
    {
        $this->trip = $trip;
        return $this;
    }

    /**
     * @return Transactions
     */
    public function getTransaction()
    {
        return $this->transaction;
    }

    /**
     * @param Transactions $transaction
     */
    public function setTransaction($transaction)
    {
        $this->transaction = $transaction;
        return $this;
    }

}

