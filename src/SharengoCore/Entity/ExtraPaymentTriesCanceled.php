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
     * @var \Transactions
     *
     * @ORM\ManyToOne(targetEntity="Transactions")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="transaction_id", referencedColumnName="id")
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
     * @var \ExtraPayments
     *
     * @ORM\ManyToOne(targetEntity="ExtraPayments")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="extra_payment_canceled_id", referencedColumnName="id")
     * })
     */
    private $extraPaymentCanceled;


}

