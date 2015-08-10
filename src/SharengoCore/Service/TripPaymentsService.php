<?php

namespace SharengoCore\Service;

use SharengoCore\Entity\Repository\TripPaymentsRepository;
use SharengoCore\Entity\TripPayments;

class TripPaymentsService
{
    /**
     * @var TripPayments
     */
    private $tripPaymentsRepository;

    /**
     * @param TripPayments
     */
    public function __construct(TripPaymentsRepository $tripPaymentsRepository)
    {
        $this->tripPaymentsRepository = $tripPaymentsRepository;
    }

    /**
     * @return [TripPayments]
     */
    public function getTripPaymentsNoInvoice()
    {
        return $this->tripPaymentsRepository->findTripPaymentsNoInvoice();
    }
}
