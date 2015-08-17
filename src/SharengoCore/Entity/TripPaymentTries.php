<?php

namespace SharengoCore\Entity;

use Cartasi\Entity\Transactions;

use Doctrine\ORM\Mapping as ORM;

/**
 * TripPaymentTries
 *
 * @ORM\Table(name="trip_payment_tries")
 * @ORM\Entity(repositoryClass="SharengoCore\Entity\Repository\TripPaymentTriesRepository")
 */
class TripPaymentTries
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="trip_payment_tries_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var TripPayments
     *
     * @ORM\ManyToOne(targetEntity="TripPayments")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="trip_payment_id", referencedColumnName="id", nullable=false)
     * })
     */
    private $tripPayment;

    /**
     * @var Webuser
     *
     * @ORM\ManyToOne(targetEntity="Webuser")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="webuser_id", referencedColumnName="id", nullable=true)
     * })
     */
    private $webuser;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="ts", type="datetime", nullable=false)
     */
    private $ts;

    /**
     * @var string can have values "OK" and "KO" or "pending" if waiting for response
     *
     * @ORM\Column(name="outcome", type="string", nullable=false)
     */
    private $outcome;

    /**
     * @var Tranasctions
     *
     * @ORM\ManyToOne(targetEntity="Cartasi\Entity\Transactions")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="transaction_id", referencedColumnName="id", nullable=true)
     * })
     */
    private $transaction;

    public function __construct(TripPayments $tripPayment, $outcome, Transactions $transaction = null)
    {
        $this->tripPayment = $tripPayment;
        $this->outcome = $outcome;
        $this->transaction = $transaction;
        $this->ts = date_create(date('Y-m-d H:i:s'));
    }

    /**
     * @return DateTime
     */
    public function getTs()
    {
        return $this->ts;
    }

    /**
     * @return Webuser
     */
    public function getWebuser()
    {
        return $this->webuser;
    }

    /**
     * @return string
     */
    public function getWebuserName()
    {
        if ($this->webuser) {
            return $this->webuser->getDisplayName();
        }
    }

    /**
     * @var string
     */
    public function getOutcome()
    {
        return $this->outcome;
    }

    /**
     * @param string $outcome
     * @return TripPaymentTries
     */
    public function setOutcome($outcome)
    {
        $this->outcome = $outcome;
        return $this;
    }
}
