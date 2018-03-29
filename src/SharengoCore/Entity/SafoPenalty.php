<?php
namespace SharengoCore\Entity;

use Doctrine\ORM\Mapping as ORM;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;

/**
 * SafoPenalty
 *
 * @ORM\Table(name="safo_penalty")
 * @ORM\Entity(repositoryClass="SharengoCore\Entity\Repository\SafoPenaltyRepository")
 */
class SafoPenalty
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="safo_penalty_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var integer
     *
     * @ORM\Column(name="penalty_id", type="integer", nullable=true)
     */
    private $penaltyId;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="insert_ts", type="datetimetz", nullable=false)
     */
    private $insertTs;

    /**
     * @var boolean
     *
     * @ORM\Column(name="charged", type="boolean", nullable=false)
     */
    private $charged;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="consumed_ts", type="datetimetz", nullable=true)
     */
    private $consumedTs;

    /**
     * @var integer
     *
     * @ORM\Column(name="customer_id", type="integer", nullable=false)
     */
    private $customerId;

    /**
     * @var integer
     *
     * @ORM\Column(name="vehicle_fleet_id", type="integer", nullable=false)
     */
    private $vehicleFleetId;

    /**
     * @var integer
     *
     * @ORM\Column(name="violation_category", type="integer", nullable=false)
     */
    private $violationCategory;

    /**
     * @var integer
     *
     * @ORM\Column(name="trip_id", type="integer", nullable=false)
     */
    private $tripId;

    /**
     * @var string
     *
     * @ORM\Column(name="car_plate", type="text", nullable=false)
     */
    private $carPlate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="violation_timestamp", type="datetimetz", nullable=false)
     */
    private $violationTimestamp;

    /**
     * @var string
     *
     * @ORM\Column(name="violation_authority", type="text", nullable=false)
     */
    private $violationAuthority;

    /**
     * @var string
     *
     * @ORM\Column(name="violation_number", type="text", nullable=false)
     */
    private $violationNumber;

    /**
     * @var string
     *
     * @ORM\Column(name="violation_description", type="text", nullable=false)
     */
    private $violationDescription;

    /**
     * @var integer
     *
     * @ORM\Column(name="rus_id", type="integer", nullable=false)
     */
    private $rusId;

    /**
     * @var integer
     *
     * @ORM\Column(name="violation_request_type", type="integer", nullable=false)
     */
    private $violationRequestType;

    /**
     * @var string
     *
     * @ORM\Column(name="violation_status", type="string", length=1, nullable=false)
     */
    private $violationStatus;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="email_sent_timestamp", type="datetimetz", nullable=true)
     */
    private $emailSentTimestamp;

    /**
     * @var boolean
     *
     * @ORM\Column(name="email_sent_ok", type="boolean", nullable=true)
     */
    private $emailSentOk;

    /**
     * @var boolean
     *
     * @ORM\Column(name="penalty_ok", type="boolean", nullable=true)
     */
    private $penaltyOk;

    /**
     * @var integer
     *
     * @ORM\Column(name="amount", type="integer", nullable=false)
     */
    private $amount = '0';

    /**
     * @var boolean
     *
     * @ORM\Column(name="complete", type="boolean", nullable=false)
     */
    private $complete = false;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getPenaltyId()
    {
        return $this->penaltyId;
    }

    /**
     * @return \DateTime
     */
    public function getInsertTs()
    {
        return $this->insertTs;
    }

    /**
     * @return bool
     */
    public function isCharged()
    {
        return $this->charged;
    }

    /**
     * @return \DateTime
     */
    public function getConsumedTs()
    {
        return $this->consumedTs;
    }

    /**
     * @return int
     */
    public function getCustomerId()
    {
        return $this->customerId;
    }

    /**
     * @return int
     */
    public function getVehicleFleetId()
    {
        return $this->vehicleFleetId;
    }

    /**
     * @return int
     */
    public function getViolationCategory()
    {
        return $this->violationCategory;
    }

    /**
     * @return int
     */
    public function getTripId()
    {
        return $this->tripId;
    }

    /**
     * @return string
     */
    public function getCarPlate()
    {
        return $this->carPlate;
    }

    /**
     * @return \DateTime
     */
    public function getViolationTimestamp()
    {
        return $this->violationTimestamp;
    }

    /**
     * @return string
     */
    public function getViolationAuthority()
    {
        return $this->violationAuthority;
    }

    /**
     * @return string
     */
    public function getViolationNumber()
    {
        return $this->violationNumber;
    }

    /**
     * @return string
     */
    public function getViolationDescription()
    {
        return $this->violationDescription;
    }

    /**
     * @return int
     */
    public function getRusId()
    {
        return $this->rusId;
    }

    /**
     * @return int
     */
    public function getViolationRequestType()
    {
        return $this->violationRequestType;
    }

    /**
     * @return string
     */
    public function getViolationStatus()
    {
        return $this->violationStatus;
    }

    /**
     * @return \DateTime
     */
    public function getEmailSentTimestamp()
    {
        return $this->emailSentTimestamp;
    }

    /**
     * @return bool
     */
    public function isEmailSentOk()
    {
        return $this->emailSentOk;
    }

    /**
     * @return bool
     */
    public function isPenaltyOk()
    {
        return $this->penaltyOk;
    }

    /**
     * @return int
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @return bool
     */
    public function isComplete()
    {
        return $this->complete;
    }


}

