<?php

namespace SharengoCore\Service;

use SharengoCore\Entity\Customers;
use SharengoCore\Entity\OldCustomerDiscount;

use Doctrine\ORM\EntityManager;

class OldCustomerDiscountsService
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    public function __construct(
        EntityManager $entityManager
    ) {
        $this->entityManager = $entityManager;
    }

    /**
     * @param Customers
     */
    public function disableCustomerDiscount(Customers $customer, $persist = true)
    {
        $this->entityManager->beginTransaction();

        try {
            $discountRate = $customer->getDiscountRate();

            $oldDiscount = new OldCustomerDiscount(
                $customer,
                $discountRate,
                date_create()
            );

            $customer->setDiscountRate(0);

            if ($persist === true) {
                $this->entityManager->persist($customer);
                $this->entityManager->persist($oldDiscount);

                $this->entityManager->flush();
            }

            $this->entityManager->commit();
        } catch (\Exception $e) {
            $this->entityManager->rollback();

            throw $e;
        }
    }
}
