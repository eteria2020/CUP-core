<?php

namespace SharengoCore\Entity;

use Doctrine\ORM\Mapping as ORM;
use Cartasi\Entity\Transactions;

/**
 * ExtraPaymentTriesCanceled
 *
 * @ORM\Table(name="extra_payment_tries_canceled")
 * @ORM\Entity(repositoryClass="SharengoCore\Entity\Repository\ExtraPaymentTriesCanceledRepository")
 */
class ExtraPaymentTriesCanceled
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="extra_payment_tries_canceled_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="inserted_ts", type="datetime", nullable=false)
     */
    private $insertedTs;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="ts", type="datetime", nullable=false)
     */
    private $ts;

    /**
     * @var string
     *
     * @ORM\Column(name="outcome", type="string", length=255, nullable=false)
     */
    private $outcome;

    /**
     * @var Transactions
     *
     * @ORM\ManyToOne(targetEntity="\Cartasi\Entity\Transactions")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="transaction_id", referencedColumnName="id", nullable=true)
     * })
     */
    private $transaction;

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
     * @var \ExtraPaymentsCanceled
     *
     * @ORM\ManyToOne(targetEntity="ExtraPaymentsCanceled")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="extra_payment_canceled_id", referencedColumnName="id")
     * })
     */
    private $extraPaymentCanceled;
    
    public function __construct(
        ExtraPaymentTries $extraPaymentTry,
        ExtraPaymentsCanceled $extraPaymentCanceled
    ) {
        $this->insertedTs = date_create();
        $this->extraPaymentCanceled = $extraPaymentCanceled;
        $this->webuser = $extraPaymentTry->getWebuser();
        $this->transaction = $extraPaymentTry->getTransaction();
        $this->ts = $extraPaymentTry->getTs();
        $this->outcome = $extraPaymentTry->getOutcome();
    }
    
    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
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
     * @return string
     */
    public function getOutcome()
    {
        return $this->outcome;
    }

    /**
     * @param string $outcome
     * @return ExtraPaymentTries
     */
    public function setOutcome($outcome)
    {
        $this->outcome = $outcome;
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
     * @return ExtraPaymentsCanceled
     */
    public function getExtraPaymentCanceled() {
        return $this->extraPaymentCanceled;
    }

}

