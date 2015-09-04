<?php

namespace SharengoCore\Service;

use SharengoCore\Entity\ExtraPayment;
use SharengoCore\Entity\Customers;

use Doctrine\ORM\EntityManager;

class ExtraPaymentsService
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param Customers $customer
     * @param int $amount
     * @param string $paymentType
     * @param string $reason
     * @return ExtraPayment
     */
    public function registerExtraPayment(
        Customers $customer,
        $amount,
        $paymentType,
        $reason
    ) {
        $extraPayment = new ExtraPayment(
            $customer,
            $amount,
            $paymentType,
            $reason
        );

        $this->entityManager->persist($extraPayment);
        $this->entityManager->flush();

        return $extraPayment;
    }
}
