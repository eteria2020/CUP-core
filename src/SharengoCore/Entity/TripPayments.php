<?php

namespace SharengoCore\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TripPayments
 *
 * @ORM\Table(name="trip_payments")
 * @ORM\Entity
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
     * @return int
     */
    public function getTotalCost()
    {
        return $this->totalCost;
    }
}
