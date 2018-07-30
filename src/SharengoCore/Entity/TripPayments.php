<?php

namespace SharengoCore\Entity;

use Doctrine\ORM\Mapping as ORM;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use SharengoCore\Exception\AlreadySetFirstPaymentTryTsException;

/**
 * TripPayments
 *
 * @ORM\Table(name="trip_payments")
 * @ORM\Entity(repositoryClass="SharengoCore\Entity\Repository\TripPaymentsRepository")
 */
class TripPayments
{
    const STATUS_TO_BE_PAYED = 'to_be_payed';
    const STATUS_TO_BE_REFUND = 'to_be_refund';
    const STATUS_PAYED_CORRECTLY = 'payed_correctly';
    const STATUS_WRONG_PAYMENT = 'wrong_payment';
    const STATUS_INVOICED = 'invoiced';

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="SEQUENCE")
     * @ORM\SequenceGenerator(sequenceName="trip_payments_id_seq", allocationSize=1, initialValue=1)
     */
    private $id;

    /**
     * @var Trips
     *
     * @ORM\OneToOne(targetEntity="Trips", inversedBy="tripPayment")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="trip_id", referencedColumnName="id", nullable=false)
     * })
     */
    private $trip;

    /**
     * @var Fares
     *
     * @ORM\ManyToOne(targetEntity="Fares")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="fare_id", referencedColumnName="id", nullable=false)
     * })
     */
    private $fare;

    /**
     * @var integer number of payable minutes of the trip, exluding bonuses and
     * free fares, but including parking
     *
     * @ORM\Column(name="trip_minutes", type="integer", nullable=false)
     */
    private $tripMinutes;

    /**
     * @var integer number of parking minutes
     *
     * @ORM\Column(name="parking_minutes", type="integer", nullable=false)
     */
    private $parkingMinutes;

    /**
     * @var integer
     *
     * @ORM\Column(name="discount_percentage", type="integer", nullable=false)
     */
    private $discountPercentage;

    /**
     * @var integer total cost (taxable + vat) in eurocents
     *
     * @ORM\Column(name="total_cost", type="integer", nullable=false)
     */
    private $totalCost;

    /**
     * @var Invoices
     *
     * @ORM\ManyToOne(targetEntity="Invoices")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="invoice_id", referencedColumnName="id", nullable=true)
     * })
     */
    private $invoice;

    /**
     * @var string can have values
     *      - to_be_payed (default)
     *      - payed_correctly
     *      - wrong_payment
     *      - invoiced
     *
     * @ORM\Column(name="status", type="string", nullable=false, options={"default" = "to_be_payed"})
     */
    private $status;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="created_at", type="datetime", nullable=false)
     */
    private $createdAt;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="payed_successfully_at", type="datetime", nullable=true)
     */
    private $payedSuccessfullyAt;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="invoiced_at", type="datetime", nullable=true)
     */
    private $invoicedAt;

    /**
     * Date from the 7 days to disabling the user are usually
     * counted equal to createdAt, but not in some cases
     *
     * @var DateTime
     *
     * @ORM\Column(name="to_be_payed_from", type="datetime", nullable=false)
     */
    private $toBePayedFrom;

    /**
     * Holds the timestamp of the first tripPaymentTries associated with this
     * tripPayments. If a user's credit card is removed and the tripPayments's
     * status is set to to_be_payed, this value shall be set to NULL untill a
     * new tripPaymentTries is created
     *
     * @var DateTime
     *
     * @ORM\Column(name="first_payment_try_ts", type="datetime", nullable=true)
     */
    private $firstPaymentTryTs;

    /**
     * @var TripPaymentTries[]
     *
     * @ORM\OneToMany(targetEntity="TripPaymentTries", mappedBy="tripPayment")
     * @ORM\OrderBy({"ts" = "ASC"})
     */
    private $tripPaymentTries;

    /**
     * @param Trips $trip
     * @param fares $fare
     * @param int $tripMinutes
     * @param int $parkingMinutes
     * @param int $discountPercentage
     * @param int $totalCost
     * @return TripPayments
     */
    public function __construct(
        Trips $trip,
        Fares $fare,
        $tripMinutes,
        $parkingMinutes,
        $discountPercentage,
        $totalCost
    ) {
        $this->trip = $trip;
        $this->fare = $fare;
        $this->tripMinutes = $tripMinutes;
        $this->parkingMinutes = $parkingMinutes;
        $this->discountPercentage = $discountPercentage;
        $this->totalCost = $totalCost;
        $this->status = self::STATUS_TO_BE_PAYED;
        $this->createdAt = date_create(date('Y-m-d H:i:s'));
        $this->toBePayedFrom = $this->createdAt;
    }

    /**
     * @param DoctrineHydrator $hydrator
     * @return mixed
     */
    public function toArray(DoctrineHydrator $hydrator)
    {
        $trip = $this->getTrip();

        $extractedTripPayment = $hydrator->extract($this);

        $extractedTripPayment['tripId'] = $trip->getId();

        $invoice = $this->getInvoice();
        if ($invoice !== null) {
            $extractedTripPayment['invoiceId'] = $invoice->getId();
        }

        unset($extractedTripPayment['fare']);
        unset($extractedTripPayment['invoice']);

        $extractedTripPayment['mustPayValue'] = $this->getCostToBePayed();

        return $extractedTripPayment;
    }

    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Trips
     */
    public function getTrip()
    {
        return $this->trip;
    }

    /**
     * @return int
     */
    public function getTripId()
    {
        return $this->trip->getId();
    }

    /**
     * @param Trip $trip
     * @return TripPayments
     */
    public function setTrip(Trips $trip)
    {
        $this->trip = $trip;
        return $this;
    }

    /**
     * @return Fares
     */
    public function getFare()
    {
        return $this->fare;
    }

    /**
     * @param Fares $fare
     * @return TripPayments
     */
    public function setFare(Fares $fare)
    {
        $this->fare = $fare;
        return $this;
    }

    /**
     * @return integer
     */
    public function getTripMinutes()
    {
        return $this->tripMinutes;
    }

    /**
     * @param integer $tripMinutes
     * @return TripPayments
     */
    public function setTripMinutes($tripMinutes)
    {
        $this->ripMinutes = $tripMinutes;
        return $this;
    }

    /**
     * @return integer
     */
    public function getParkingMinutes()
    {
        return $this->parkingMinutes;
    }

    /**
     * @param integer $parkingMinutes
     * @return TripPayments
     */
    public function setParkingMinutes($parkingMinutes)
    {
        $this->parkingMinutes = $parkingMinutes;
        return $this;
    }

    /**
     * @return integer
     */
    public function getDiscountPercentage()
    {
        return $this->discountPercentage;
    }

    /**
     * @param integer $discountPercentage
     * @return TripPayments
     */
    public function setDiscountPercentage($discountPercentage)
    {
        $this->discountPercentage = $discountPercentage;
        return $this;
    }

    /**
     * @return integer
     */
    public function getTotalCost()
    {
        return $this->totalCost;
    }

    /**
     * @return string
     */
    public function getFormattedTotalCost()
    {
        return floor($this->totalCost / 100) .
            ',' .
            ($this->totalCost % 100 < 10 ? '0' : '') .
            $this->totalCost % 100 .
            '€';
    }

    /**
     * @param integer $totalCost
     * @return TripPayments
     */
    public function setTotalCost($totalCost)
    {
        $this->totalCost = $totalCost;
        return $this;
    }

    /**
     * @return Invoices
     */
    public function getInvoice()
    {
        return $this->invoice;
    }

    /**
     * @param Invoices $invoice
     * @return TripPayments
     */
    public function setInvoice(Invoices $invoice)
    {
        $this->invoice = $invoice;
        $this->setInvoicedAt($invoice->getGeneratedTs());
        $this->setStatus(self::STATUS_INVOICED);
        return $this;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param string $status
     * @return TripPayments
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTime $createdAt
     * @return TripPayments
     */
    public function setcreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getPayedSuccessfullyAt()
    {
        return $this->payedSuccessfullyAt;
    }

    /**
     * @param \DateTime $payedSuccessfullyAt
     * @return TripPayments
     */
    public function setPayedSuccessfullyAt($payedSuccessfullyAt)
    {
        $this->payedSuccessfullyAt = $payedSuccessfullyAt;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getInvoicedAt()
    {
        return $this->invoicedAt;
    }

    /**
     * @param \DateTime $invoicedAt
     * @return TripPayments
     */
    private function setInvoicedAt($invoicedAt)
    {
        $this->invoicedAt = $invoicedAt;
        return $this;
    }

    /**
     * @return TripPayments
     */
    public function setPayedCorrectly()
    {
        $this->setStatus(self::STATUS_PAYED_CORRECTLY);
        $this->payedSuccessfullyAt = date_create();

        return $this;
    }

    /**
     * @return TripPayments
     */
    public function setWrongPayment()
    {
        return $this->setStatus(self::STATUS_WRONG_PAYMENT);
    }

    /**
     * @return boolean
     */
    public function isWrongPayment()
    {
        return $this->status === self::STATUS_WRONG_PAYMENT;
    }

    /**
     * @return TripPaymentTries[]
     */
    public function getTripPaymentTries()
    {
        return $this->tripPaymentTries;
    }

    /**
     * @return Customers
     */
    public function getCustomer()
    {
        return $this->trip->getCustomer();
    }

    /**
     * @return int eurocents still to be payed
     */
    public function getCostToBePayed()
    {
        if ($this->status === self::STATUS_PAYED_CORRECTLY || $this->status === self::STATUS_INVOICED) {
            return 0;
        }

        return $this->totalCost;
    }

    /**
     * @return DateTime
     */
    public function getToBePayedFrom()
    {
        return $this->toBePayedFrom;
    }

    /**
     * @return DateTime
     */
    public function getFirstPaymentTryTs()
    {
        return $this->firstPaymentTryTs;
    }

    /**
     * Sets the value of firstPaymentTryTs. If the value of firstPaymentTryTs
     * is already set, throws exception AlreadySetFirstPaymentTryTsException.
     * To prevent this, use method isFirstPaymentTryTsSet()
     *
     * @param DateTime $firstPaymentTryTs
     * @return TripPayments
     * @throws AlreadySetFirstPaymentTryTsException
     */
    public function setFirstPaymentTryTs($firstPaymentTryTs)
    {
        if ($this->isFirstPaymentTryTsSet()) {
            throw new AlreadySetFirstPaymentTryTsException();
        }
        $this->firstPaymentTryTs = $firstPaymentTryTs;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isFirstPaymentTryTsSet()
    {
        return $this->firstPaymentTryTs !== null;
    }
}
