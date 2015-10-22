<?php

namespace SharengoCore\Service;

use Cartasi\Service\CartasiCustomerPaymentsInterface;
use SharengoCore\Entity\Customers;
use SharengoCore\Entity\CustomersBonusPackages;

use Doctrine\ORM\EntityManager;

class BuyCustomerBonusPackage
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var CartasiCustomerPaymentsInterface
     */
    private $payments;

    public function __construct(
        EntityManager $entityManager,
        CartasiCustomerPaymentsInterface $payments
    ) {
        $this->entityManager = $entityManager;
        $this->payments = $payments;
    }

    /**
     * @param Customers $customer
     * @param CustomersBonusPackages $package
     * @return bool whether the operation concluded positively
     */
    public function __invoke(
        Customers $customer,
        CustomersBonusPackages $package
    ) {
        $this->entityManager->beginTransaction();

        try {
            $cartasiResponse = $this->payments->sendPaymentRequest($customer, $package->getCost());

            if ($cartasiResponse->getCompletedCorrectly()) {
                $bonus = $package->generateCustomerBonus($customer);

                $this->entityManager->persist($bonus);
                $this->entityManager->flush();
            } else {
                return false;
            }

            $this->entityManager->commit();
        } catch (\Exception $e) {
            $this->entityManager->rollback();
            return false;
        }

        return true;
    }
}
