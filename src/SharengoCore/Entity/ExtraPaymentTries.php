<?php

namespace SharengoCore\Entity;

use Cartasi\Entity\Transactions;

use Doctrine\ORM\Mapping as ORM;

/**
 * ExtraPaymentTries
 *
 * @ORM\Table(name="extra_payment_tries")
 * @ORM\Entity(repositoryClass="SharengoCore\Entity\Repository\ExtraPaymentTriesRepository")
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
     * @var Transactions
     *
     * @ORM\ManyToOne(targetEntity="Cartasi\Entity\Transactions")
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
     * @var \ExtraPayments
     *
     * @ORM\ManyToOne(targetEntity="ExtraPayments")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="extra_payment_id", referencedColumnName="id")
     * })
     */
    private $extraPayment;
    
    /**
     * @param ExtraPayments $extraPayment
     * @param string $outcome
     * @param Transactions|null $transaction
     * @param Webuser|null $webuser
     */
    public function __construct(ExtraPayments $extraPayment, $outcome, Transactions $transaction = null, Webuser $webuser = null)
    {
        error_log("dentro al costruttore della tries");
        $this->extraPayment = $extraPayment;
        $this->outcome = $outcome;
        $this->transaction = $transaction;
        $this->webuser = $webuser;
        $this->ts = date_create(date('Y-m-d H:i:s'));
        //error_log(var_dump($this));
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

}

