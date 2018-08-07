<?php

namespace SharengoCore\Entity;

use SharengoCore\Exception\AlreadySetFirstPaymentTryTsException;

use Doctrine\ORM\Mapping as ORM;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;

/**
 * TripPaymentsCanceled
 *
 * @ORM\Table(name="trip_payments_canceled")
 * @ORM\Entity(repositoryClass="SharengoCore\Entity\Repository\TripPaymentsCanceledRepository")
 */
class TripPaymentsCanceled
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
     * @var DateTime Inserted timestamp of the TripPaymentsCanceled
     *
     * @ORM\Column(name="inserted_ts", type="datetime", nullable=false)
     */
    private $insertedTs;

    /**
     * @var Webuser Webuser who edited the trip, causing this backup copy to be
     * generated
     *
     * @ORM\ManyToOne(targetEntity="Webuser")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="webuser_id", referencedColumnName="id", nullable=false)
     * })
     */
    private $webuser;

    /**
     * @var DateTime timestampEnd of the trip before the edit
     *
     * @ORM\Column(name="original_end_ts", type="datetime", nullable=false)
     */
    private $originalEndTs;

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
     * @var DateTime
     *
     * @ORM\Column(name="created_at", type="datetime", nullable=false)
     */
    private $createdAt;

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
     * @var TripPaymentTriesCanceled[]
     *
     * @ORM\OneToMany(targetEntity="TripPaymentTriesCanceled", mappedBy="tripPaymentCanceled")
     */
    private $tripPaymentTriesCanceled;

    /**
     * @var \Partners
     *
     * @ORM\ManyToOne(targetEntity="Partners")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="partner_id", referencedColumnName="id", nullable=true)
     * })
     */
    private $partner;

    /**
     * @param TripPayments $tripPayment
     * @param Webuser $webuser
     * @return TripPaymentsCanceled
     */
    public function __construct(TripPayments $tripPayment, Webuser $webuser)
    {
        $this->insertedTs = date_create();
        $this->webuser = $webuser;
        $this->trip = $tripPayment->getTrip();
        $this->originalEndTs = $this->trip->getTimestampEnd();
        $this->fare = $tripPayment->getFare();
        $this->tripMinutes = $tripPayment->getTripMinutes();
        $this->parkingMinutes = $tripPayment->getParkingMinutes();
        $this->discountPercentage = $tripPayment->getDiscountPercentage();
        $this->totalCost = $tripPayment->getTotalCost();
        $this->createdAt = $tripPayment->getCreatedAt();
        $this->toBePayedFrom = $tripPayment->getToBePayedFrom();
        $this->firstPaymentTryTs = $tripPayment->getFirstPaymentTryTs();
    }

        /**
     * @return Partner
     */
    public function getPartner()
    {
        return $this->partner;
    }

    /**
     * 
     * @param \SharengoCore\Entity\Partners $partner
     */
    public function setPartner(Partners $partner = null) {
        $this->partner = $partner;
    }
}
