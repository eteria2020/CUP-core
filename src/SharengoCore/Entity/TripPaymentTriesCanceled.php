<?php

namespace SharengoCore\Entity;

use Cartasi\Entity\Transactions;

use Doctrine\ORM\Mapping as ORM;

/**
 * TripPaymentTriesCanceled
 *
 * @ORM\Table(name="trip_payment_tries_canceled")
 * @ORM\Entity(repositoryClass="SharengoCore\Entity\Repository\TripPaymentTriesCanceledRepository")
 */
class TripPaymentTriesCanceled
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
     * @var DateTime Inserted timestamp of the TripPaymentTriesCanceled
     *
     * @ORM\Column(name="inserted_ts", type="datetime", nullable=false)
     */
    private $insertedTs;

    /**
     * @var TripPaymentsCanceled
     *
     * @ORM\ManyToOne(targetEntity="TripPaymentsCanceled", inversedBy="tripPaymentTriesCanceled")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="trip_payment_canceled_id", referencedColumnName="id", nullable=false)
     * })
     */
    private $tripPaymentCanceled;

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
     * @var Tranasctions
     *
     * @ORM\ManyToOne(targetEntity="Cartasi\Entity\Transactions")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="transaction_id", referencedColumnName="id", nullable=true)
     * })
     */
    private $transaction;

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
     * @param TripPaymentTries $tripPaymentTry
     * @param TripPaymentsCanceled $tripPaymentCanceled
     */
    public function __construct(
        TripPaymentTries $tripPaymentTry,
        TripPaymentsCanceled $tripPaymentCanceled
    ) {
        $this->insertedTs = date_create();
        $this->tripPaymentCanceled = $tripPaymentCanceled;
        $this->webuser = $tripPaymentTry->getWebuser();
        $this->transaction = $tripPaymentTry->getTransaction();
        $this->ts = $tripPaymentTry->getTs();
        $this->outcome = $tripPaymentTry->getOutcome();
    }
}
