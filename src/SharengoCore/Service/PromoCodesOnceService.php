<?php

namespace SharengoCore\Service;

use Doctrine\ORM\EntityManager;
use SharengoCore\Service\CustomersService;
use SharengoCore\Entity\Repository\CustomersRepository;
use SharengoCore\Entity\CustomersBonus;
use SharengoCore\Entity\Repository\PromoCodesRepository;
use SharengoCore\Entity\Repository\PromoCodesInfoRepository;
use SharengoCore\Entity\Repository\PromoCodesOnceRepository;
use SharengoCore\Service\SimpleLoggerService as Logger;

use SharengoCore\Exception\PromoCodeOnceExpired;
use SharengoCore\Exception\PromoCodeOnceNotActive;
use SharengoCore\Exception\PromoCodeOnceNotFound;
use SharengoCore\Exception\PromoCodeOnceUsed;

class PromoCodesOnceService
{
     /**
     * @var CustomersService
     */
    private $customersService;

     /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var CustomersRepository
     */
    private $customersRepository;

    /**
     * @var PromoCodesRepository
     */
    private $pcRepository;

    /**
     * @var PromoCodesOnceRepository
     */
    private $pcoRepository;

    /**
     * @var PromoCodesInfoRepository
     */
    private $pcInfoRepository;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @param PromoCodesRepository $pcoRepository
     * @param PromoCodesInfoRepository $pcInfoRepository
     */
    public function __construct(
        CustomersService $customersService,
        EntityManager $entityManager,
        CustomersRepository $customersRepository,
        PromoCodesRepository $pcRepository,
        PromoCodesOnceRepository $pcoRepository,
        PromoCodesInfoRepository $pcInfoRepository,
        Logger $logger
    ) {
        $this->customersService = $customersService;
        $this->entityManager = $entityManager;
        $this->customersRepository = $customersRepository;
        $this->pcRepository = $pcRepository;
        $this->pcoRepository = $pcoRepository;
        $this->pcInfoRepository = $pcInfoRepository;
        $this->logger = $logger;
    }

    /**
     * @param string $promocode
     * @return PromoCodesOnce
     */
    public function getByPromoCode($promocode) {
        return $this->pcoRepository->findByPromoCode($promocode);
    }

     /**
     * @param string $promocode
     * @return isValid
     */
    public function isValid($promocode) {
        $result= FALSE;

        $this->logger->setOutputEnvironment(Logger::OUTPUT_ON);
        $this->logger->setOutputType(Logger::TYPE_CONSOLE);

        //$this->logger->setOutputType(Logger::OUTPUT_DEV);

        $promocodeUpper = strtoupper($promocode);
         $promoCodesOnce = $this->getByPromoCode($promocodeUpper);
         if($promoCodesOnce!==NULL){                    // find promocode once

            if($promoCodesOnce->getUsedTs()===NULL) {   // promocode not used
                $promoCodesInfo = $promoCodesOnce->getPromoCodesInfo();

                if($promoCodesInfo->getActive()){       // promocode info is active
                    $now = new \DateTime();

                    if($now>=$promoCodesInfo->getValidFrom() &&
                            $now<=$promoCodesInfo->getValidTo()){   // is not expired
                       //$this->logger->log("update\n");
                       $result=TRUE;
                    }
                    else {
                       $this->logger->log("promo code ".$promocode." exiped\n");
                    }
                }
                else {
                    $this->logger->log("promo code ".$promocode." not active\n");
                }
            }
            else {
                $this->logger->log("promo code once ".$promocode." used\n");
            }
         } else {
            $this->logger->log("promo code once ".$promocode." not found\n");
         }
        return $result;
    }

    /**
     * @param  PromoCode
     * @return checkPromoCodeOnce
     */
     public function checkPromoCodeOnce($promocode)
    {
        $result= FALSE;

        $this->logger->setOutputEnvironment(Logger::OUTPUT_ON);
        $this->logger->setOutputType(Logger::TYPE_CONSOLE);

        $promocodeUpper = strtoupper($promocode);
        $promoCodesOnce = $this->getByPromoCode($promocodeUpper);
        if($promoCodesOnce!==NULL){                    // find promocode once

            if($promoCodesOnce->getUsedTs()===NULL) {   // promocode not used
                $promoCodesInfo = $promoCodesOnce->getPromoCodesInfo();

                if($promoCodesInfo->getActive()){       // promocode info is active
                    $now = new \DateTime();

                    if($now>=$promoCodesInfo->getValidFrom() &&
                            $now<=$promoCodesInfo->getValidTo()){   // is not expired
                       //$this->logger->log("update\n");
                       $result=TRUE;
                    }
                    else {
                       $this->logger->log("promo code ".$promocode." exiped\n");
                       throw new PromoCodeOnceExpired();
                    }
                }
                else {
                    $this->logger->log("promo code ".$promocode." not active\n");
                    throw new PromoCodeOnceNotActive();
                }
            }
            else {
                $this->logger->log("promo code once ".$promocode." used\n");
                throw new PromoCodeOnceUsed();
            }
        } else {
             $this->logger->log("promo code once ".$promocode." not found\n");
             throw new PromoCodeOnceNotFound();
        }
        return $result;
    }

    /**
     * @param  Customer
     * @param  PromoCode
     * @return PromoCodesOnce
     */
    public function usePromoCode($customer, $promocode) {
        $promocodeUpper = strtoupper($promocode);
        $promoCodesOnce = $this->pcoRepository->findByPromoCode($promocodeUpper);

        if($promoCodesOnce!==NULL){
            $promoCodeInfo = $promoCodesOnce->getPromoCodesInfo();

            $promoCode = $this->pcRepository->getPromoCodeByPromocodeInfo($promoCodeInfo->getId());
             $customersBonus = CustomersBonus::createFromPromoCode($promoCode);
            $this->customersService->addBonus($customer, $customersBonus);

            $promoCodesOnce->setCustomer($customer);
            $promoCodesOnce->setCustomerBonus($customersBonus);
            $promoCodesOnce->setUsedTs(date_create());
            $this->entityManager->persist($promoCodesOnce);
            $this->entityManager->flush();
        }

        return $promoCodesOnce;
    }
}
