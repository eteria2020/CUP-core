<?php

namespace SharengoCore\Service;

use Cartasi\Service\CartasiCustomerPaymentsInterface;
use Cartasi\Service\CartasiContractsService;
use GPWebpay\Service\GPWebpayCustomerPayments;
use Mollie\Service\MollieCustomerPayments;
use Bankart\Service\BankartCustomerPayments;
use SharengoCore\Entity\Customers;
use SharengoCore\Entity\CustomersBonusPackages;
use SharengoCore\Entity\CustomersPoints;
use SharengoCore\Entity\BonusPackagePayment;
use SharengoCore\Service\CustomersPointsService;
use Doctrine\ORM\EntityManager;

class BuyCustomerBonusPackage {

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

    /**
     * @var CartasiContractsService
     */
    private $cartasiContractService;

    /**
     * @var GPWebpayCustomerPayments
     */
    private $gpwebpayCustomerPayments;

    /**
     * @var MollieCustomerPayments
     */
    private $mollieCustomerPayments;

    /**
     * @var BankartCustomerPayments
     */
    private $bankartCustomerPayments;

    public function __construct(
            EntityManager $entityManager,
            CartasiCustomerPaymentsInterface $payments,
            CustomersPointsService $customersPointsService,
            CartasiContractsService $cartasiContractService,
            $gpwebpayCustomerPayments,
            $mollieCustomerPayments,
            $bankartCustomerPayments
    ) {
        $this->entityManager = $entityManager;
        $this->payments = $payments;
        $this->customersPointsService = $customersPointsService;
        $this->cartasiContractService = $cartasiContractService;
        $this->gpwebpayCustomerPayments = $gpwebpayCustomerPayments;
        $this->mollieCustomerPayments = $mollieCustomerPayments;
        $this->bankartCustomerPayments = $bankartCustomerPayments;
    }

    /**
     * @param Customers $customer
     * @param CustomersBonusPackages $package
     * @param bool $no_pay MYSHARENGO
     * @return bool whether the operation concluded positively
     */
    public function __invoke(
            Customers $customer,
            CustomersBonusPackages $package,
            $no_pay = false
    ) {
        $this->entityManager->beginTransaction();

        try {
            if ($package->getType() === "Pacchetto" && ($package->getCode() == "WOMEN" && (date_create() >= date_create('2018-03-08 06:00:00') && date_create() <= date_create('2018-03-09 04:55:00')))) {
                $bonus = $package->generateCustomerWomenBonus($customer);
                $this->entityManager->persist($bonus);
                $this->entityManager->flush();
            } elseif ($package->getType() === "Pacchetto") {

                if ($no_pay) { // MYSHARENGO
                    if ($package->getCode() == "WOMEN") { //check if package women
                        $bonus = $package->generateCustomerWomenBonus($customer);
                    } else {
                        $bonus = $package->generateCustomerBonus($customer);
                    }
                                        
                    $this->entityManager->persist($bonus);

                    $this->entityManager->flush();
                } else {
                    $contract = $this->cartasiContractService->getCartasiContract($customer);

                    if (!is_null($contract->getPartner())) {
                        if ($contract->getPartner()->getCode() == "gpwebpay") {
                            $cartasiResponse = $this->gpwebpayCustomerPayments->sendPaymentRequest($customer, $package->getCost());
                        } elseif ($contract->getPartner()->getCode() == "mollie") {
                            $cartasiResponse = $this->mollieCustomerPayments->sendPaymentRequest($customer, $package->getCost());
                        } elseif ($contract->getPartner()->getCode() == "bankart") {
                            $cartasiResponse = $this->bankartCustomerPayments->sendPaymentRequest($customer, $package->getCost());
                        }
                    } else {
                        $cartasiResponse = $this->payments->sendPaymentRequest($customer, $package->getCost());
                    }

                    if ($cartasiResponse->getCompletedCorrectly()) {
                        if ($package->getCode() == "WOMEN") { //check if package women
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
                    } else {
                        return false;
                    }
                }
            } else { //$package->getType() === "PacchettoPunti"
                if ($this->customersPointsService->getTotalPoints($customer->getId()) >= $package->getCost()) {

                    $customersPoints = new CustomersPoints();
                    $customersPoints = $this->customersPointsService->setCustomerPointPackage($customersPoints, $customer, $package);
                    $bonus = $package->generateCustomerBonus($customer);

                    $this->entityManager->persist($bonus);
                    $this->entityManager->persist($customersPoints);
                    $this->entityManager->flush();
                } else {
                    return false;
                }
            }

            $this->entityManager->commit();
        } catch (\Exception $e) {
            $this->entityManager->rollback();
            return false;
        }

        return true;
    }

}
