<?php



use Doctrine\ORM\Mapping as ORM;

/**
 * ExtraPaymentsCanceled
 *
 * @ORM\Table(name="extra_payments_canceled", indexes={@ORM\Index(name="IDX_492E4DFA9395C3F3", columns={"customer_id"}), @ORM\Index(name="IDX_492E4DFA4B061DF9", columns={"fleet_id"}), @ORM\Index(name="IDX_492E4DFA49279951", columns={"webuser_id"}), @ORM\Index(name="IDX_492E4DFA2FC0CB0F", columns={"transaction_id"})})
 * @ORM\Entity
 */
class ExtraPaymentsCanceled
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="extra_payments_canceled_id_seq", allocationSize=1, initialValue=1)
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
     * @ORM\Column(name="original_end_ts", type="datetime", nullable=false)
     */
    private $originalEndTs;

    /**
     * @var integer
     *
     * @ORM\Column(name="amount", type="integer", nullable=false)
     */
    private $amount;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="generated_ts", type="datetime", nullable=false)
     */
    private $generatedTs;

    /**
     * @var string
     *
     * @ORM\Column(name="reasons", type="string", nullable=false)
     */
    private $reasons;

    /**
     * @var string
     *
     * @ORM\Column(name="payment_type", type="string", nullable=false)
     */
    private $paymentType;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="first_extra_try_ts", type="datetime", nullable=true)
     */
    private $firstExtraTryTs;

    /**
     * @var \Customers
     *
     * @ORM\ManyToOne(targetEntity="Customers")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="customer_id", referencedColumnName="id")
     * })
     */
    private $customer;

    /**
     * @var \Fleets
     *
     * @ORM\ManyToOne(targetEntity="Fleets")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="fleet_id", referencedColumnName="id")
     * })
     */
    private $fleet;

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
     * @var \Transactions
     *
     * @ORM\ManyToOne(targetEntity="Transactions")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="transaction_id", referencedColumnName="id")
     * })
     */
    private $transaction;


}

