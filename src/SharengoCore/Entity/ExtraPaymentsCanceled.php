<?php



use Doctrine\ORM\Mapping as ORM;

/**
 * ExtraPaymentsCanceled
 *
 * @ORM\Table(name="extra_payments_canceled")
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
     * @var integer
     *
     * @ORM\Column(name="webuser_id", type="integer", nullable=false)
     */
    private $webuserId;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="original_end_ts", type="datetime", nullable=false)
     */
    private $originalEndTs;

    /**
     * @var integer
     *
     * @ORM\Column(name="customer_id", type="integer", nullable=false)
     */
    private $customerId;

    /**
     * @var integer
     *
     * @ORM\Column(name="amount", type="integer", nullable=false)
     */
    private $amount;

    /**
     * @var integer
     *
     * @ORM\Column(name="fleet_id", type="integer", nullable=false)
     */
    private $fleetId = '1';

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="generated_ts", type="datetime", nullable=false)
     */
    private $generatedTs;

    /**
     * @var integer
     *
     * @ORM\Column(name="transaction_id", type="integer", nullable=true)
     */
    private $transactionId;

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


}

