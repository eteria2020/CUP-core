<?php

namespace SharengoCore\Service;

use Cartasi\Service\CartasiCustomerPaymentsInterface;
use SharengoCore\Entity\Customers;
use SharengoCore\Entity\CustomersBonusPackages;
use SharengoCore\Entity\BonusPackagePayment;
use SharengoCore\Entity\CustomersPoints;
use SharengoCore\Service\CustomersPointsService;

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
    
    /**
     * @var CustomersPointsService
     */
    private $customersPointsService;

    public function __construct(
        EntityManager $entityManager,
        CartasiCustomerPaymentsInterface $payments,
        CustomersPointsService $customersPointsService
    ) {
        $this->entityManager = $entityManager;
        $this->payments = $payments;
        $this->customersPointsService = $customersPointsService;
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
        $result = false;
        $this->entityManager->beginTransaction();

        try {
            if($package->getType() === "Pacchetto" &&
                    ($package->getCode()=="WOMEN" &&
                    (date_create() >= date_create(date("Y").'-03-08 06:00:00') && date_create() <= date_create(date("Y").'-03-09 04:55:00')))){
                $bonus = $package->generateCustomerWomenBonus($customer);
                $this->entityManager->persist($bonus);
                $this->entityManager->flush();
            }
            else if($package->getType() === "Pacchetto"){

                $cartasiResponse = $this->payments->sendPaymentRequest($customer, $package->getCost());

                if ($cartasiResponse->getCompletedCorrectly()) {
                    if ($package->getCode()=="WOMEN") { //check if package women
                        $bonus = $package->generateCustomerWomenBonus($customer);
                    } else {
                        $bonus = $package->generateCustomerBonus($customer);
                    }
                    //$bonus = $package->generateCustomerBonus($customer);
                    $bonusPayment = new BonusPackagePayment(
                        $customer,
                        $bonus,
                        $package,
                        $cartasiResponse->getTransaction()
                    );

                    $this->entityManager->persist($bonus);
                    $this->entityManager->persist($bonusPayment);
                    $this->entityManager->flush();
                    $result = true;
                } else {
                    return $result;
                }
            } else if($package->getType() === "Wallet"){

                $result = $this->buyPackageWallet($customer, $package);

            } else { //$package->getType() === "PacchettoPunti"

                if($this->customersPointsService->getTotalPoints($customer->getId()) >= $package->getCost()){
                    
                    $customersPoints = new CustomersPoints();
                    $customersPoints = $this->customersPointsService->setCustomerPointPackage($customersPoints, $customer, $package);
                    $bonus = $package->generateCustomerBonus($customer);

                    $this->entityManager->persist($bonus);
                    $this->entityManager->persist($customersPoints);
                    $this->entityManager->flush();
                    $result = true;
                } else{
                    return $result;
                }
            }

            $this->entityManager->commit();
        } catch (\Exception $e) {
            $this->entityManager->rollback();
        }

        return $result;
    }

    /**
     * 
     * @param Customers $customer
     * @param CustomersBonusPackages $package
     * @return boolean
     */
    private function buyPackageWallet(Customers $customer, CustomersBonusPackages $package) {
        $result= false;

        $cartasiResponse = $this->payments->sendPaymentRequest($customer, $package->getCost());

        if ($cartasiResponse->getCompletedCorrectly()) {

            $bonus = $package->generateCustomerBonus($customer);
            $bonusPayment = new BonusPackagePayment(
                $customer,
                $bonus,
                $package,
                $cartasiResponse->getTransaction()
            );

            $this->entityManager->persist($bonus);
            $this->entityManager->persist($bonusPayment);
            $this->entityManager->flush();
            $result = true;
        }
        return $result;
    }
}
