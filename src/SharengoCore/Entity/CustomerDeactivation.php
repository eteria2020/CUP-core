<?php

namespace SharengoCore\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CustomerDeactivation
 *
 * @ORM\Table(name="customer_deactivations")
 * @ORM\Entity(repositoryClass="SharengoCore\Entity\Repository\CustomerDeactivationRepository")
 */
class CustomerDeactivation
{
    /**
     * @var string
     */
    const FIRST_PAYMENT_NOT_COMPLETED = 'FIRST_PAYMENT_NOT_COMPLETED';

    /**
     * @var string
     */
    const FAILED_PAYMENT = 'FAILED_PAYMENT';

    /**
     * @var string
     */
    const INVALID_DRIVERS_LICENSE = 'INVALID_DRIVERS_LICENSE';

    /**
     * @var string
     */
    const DISABLED_BY_WEBUSER = 'DISABLED_BY_WEBUSER';

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="customer_deactivations_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="inserted_ts", type="datetimetz", nullable=false)
     */
    private $insertedTs;

    /**
     * @var Customers
     *
     * @ORM\ManyToOne(targetEntity="Customers")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="customer_id", referencedColumnName="id", nullable=false)
     * })
     */
    private $customer;

    /**
     * @var string
     *
     * @ORM\Column(name="reason", type="text", nullable=false)
     */
    private $reason;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="start_ts", type="datetimetz", nullable=false)
     */
    private $startTs;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="end_ts", type="datetimetz", nullable=true)
     */
    private $endTs;

    /**
     * @var Webuser
     *
     * @ORM\ManyToOne(targetEntity="Webuser")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="deactivator_webuser_id", referencedColumnName="id", nullable=true)
     * })
     */
    private $deactivatorWebuser;

    /**
     * @var Webuser
     *
     * @ORM\ManyToOne(targetEntity="Webuser")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="reactivator_webuser_id", referencedColumnName="id", nullable=true)
     * })
     */
    private $reactivatorWebuser;

    /**
     * @var array
     *
     * @ORM\Column(name="details", type="json_array", nullable=false)
     */
    private $details = [];

    /**
     * @param Customers $customer
     * @param string $reason
     * @param array $details
     * @param Webuser|null $webuser
     * @param \DateTime|null $startTs
     */
    public function __construct(
        Customers $customer,
        $reason,
        array $details,
        \DateTime $startTs = null,
        Webuser $webuser = null
    ) {
        $this->insertedTs = date_create();
        $this->customer = $customer;
        $this->reason = $reason;
        $this->startTs = ($startTs === null) ? $this->insertedTs : $startTs;
        $this->deactivatorWebuser = $webuser;
        $this->details = [];
        $this->details['deactivation'] = $details;
    }

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Customers
     */
    public function getCustomer()
    {
        return $this->customer;
    }

    /**
     * @return string
     */
    public function getReason()
    {
        return $this->reason;
    }

    /**
     * @return \DateTime
     */
    public function getStartTs()
    {
        return $this->startTs;
    }

    /**
     * @param array $details
     * @param \DateTime|null $endTs
     * @param Webuser|null $webuser
     */
    public function reactivate(
        array $details,
        \DateTime $endTs = null,
        Webuser $webuser = null
    ) {
        $this->endTs = ($endTs === null) ? date_create() : $endTs;
        $this->reactivatorWebuser = $webuser;
        $this->details['reactivation'] = $details;
    }

    /**
     * @return boolean wether this CustomerDeactivation should be currently
     *     keeping the Customer deactivated
     */
    public function isEffective()
    {
        $now = date_create();
        return $this->startTs < $now &&
            ($this->endTs === null) ? true : $now < $this->endTs;
    }
}
