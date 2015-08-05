<?php

namespace SharengoCore\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TripPaymentTries
 *
 * @ORM\Table(name="trip_payment_tries")
 * @ORM\Entity
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
     *   @ORM\JoinColumn(name="webuser_id", referencedColumnName="id", nullable=false)
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
     * @var string can have values "OK" and "KO"
     *
     * @ORM\Column(name="outcome", type="string", nullable=false)
     */
    private $outcome;

    /**
     * @var Tranasctions
     *
     * @ORM\ManyToOne(targetEntity="Cartasi\Entity\Transactions")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="transaction_id", referencedColumnName="id", nullable=false)
     * })
     */
    private $transaction;
}
