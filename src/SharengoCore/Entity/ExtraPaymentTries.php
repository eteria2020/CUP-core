<?php



use Doctrine\ORM\Mapping as ORM;

/**
 * ExtraPaymentTries
 *
 * @ORM\Table(name="extra_payment_tries", indexes={@ORM\Index(name="idx_transaction_id", columns={"transaction_id"}), @ORM\Index(name="idx_webuser_id", columns={"webuser_id"}), @ORM\Index(name="idx_extra_payment_id", columns={"extra_payment_id"})})
 * @ORM\Entity
 */
class ExtraPaymentTries
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="extra_payment_tries_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

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
     *   @ORM\JoinColumn(name="extra_payment_id", referencedColumnName="id")
     * })
     */
    private $extraPayment;


}

