<?php

namespace SharengoCore\Entity;

use Doctrine\ORM\Mapping as ORM;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;

/**
 * TripPayments
 *
 * @ORM\Table(name="trip_payments")
 * @ORM\Entity(repositoryClass="SharengoCore\Entity\Repository\TripPaymentsRepository")
 */
class TripPayments
{
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
     * @ORM\ManyToOne(targetEntity="Trips")
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
     *      - not_payed (default)
     *      - payed_correctly
     *      - wrong_payment
     *      - invoiced
     *
     * @ORM\Column(name="status", type="string", nullable=false, options={"default" = "not_payed"})
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
        $this->status = 'not_payed';
        $this->createdAt = date_create(date('Y-m-d H:i:s'));
    }

    /**
     * @param DoctrineHydrator $hydrator
     * @return mixed
     */
    public function toArray(DoctrineHydrator $hydrator)
    {
        $trip = $this->getTrip();
        if ($trip !== null) {
            $trip = $trip->getId();
        }

        $fare = $this->getFare();
        if ($fare !== null) {
            $fare = $fare->toArray($hydrator);
        }

        $invoice = $this->getInvoice();
        if ($invoice !== null) {
            $invoice = $invoice->toArray($hydrator);
        }

        $extractedTripPayment = $hydrator->extract($this);
        $extractedTripPayment['trip'] = $trip;
        $extractedTripPayment['fare'] = $fare;
        $extractedTripPayment['invoice'] = $invoice;

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
    public function setInvoicedAt($invoicedAt)
    {
        $this->invoicedAt = $invoicedAt;
        return $this;
    }

    /**
     * @return TripPayments
     */
    public function setPayedCorrectly()
    {
        return $this->setStatus('payed_correctly');
    }

    /**
     * @return TripPayments
     */
    public function setWrongPayment()
    {
        return $this->setStatus('wrong_payment');
    }
}
