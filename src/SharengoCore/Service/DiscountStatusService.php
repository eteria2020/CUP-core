<?php

namespace SharengoCore\Service;

use SharengoCore\Entity\Customers;
use SharengoCore\Entity\DiscountStatus;

use Doctrine\Orm\EntityManager;

class DiscountStatusService
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function upsertStatus(Customers $customer, $status)
    {
        if ($customer->hasDiscountStatus()) {
            $this->updateStatus($customer, $status);
        } else {
            $this->insertStatus($customer, $status);
        }
    }

    private function insertStatus(Customers $customer, $status)
    {
        $discountStatus = new DiscountStatus($customer, $status);

        $this->entityManager->persist($discountStatus);
        $this->entityManager->flush();
    }

    private function updateStatus(Customers $customer, $status)
    {
        $discountStatus = $customer->discountStatus();

        $discountStatus->updateStatus($status);

        $this->entityManager->persist($discountStatus);
        $this->entityManager->flush();
    }
}
