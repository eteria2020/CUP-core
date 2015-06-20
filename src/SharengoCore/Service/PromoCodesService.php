<?php

namespace SharengoCore\Service;

use SharengoCore\Entity\Repository\PromoCodesRepository;
use SharengoCore\Entity\Repository\PromoCodesInfoRepository;

class PromoCodesService
{
    private $pcRepository;
    private $pcInfoRepository;

    public function __construct(PromoCodesRepository $pcRepository,
                                PromoCodesInfoRepository $pcInfoRepository)
    {
        $this->pcRepository = $pcRepository;
        $this->pcInfoRepository = $pcInfoRepository;
    }

    public function getPromoCode($promoCode) {
        return $this->pcRepository->findOneByPromocode($promoCode);
    }

    public function isValid($promoCode) {

        // get promocode from db
        $activePromoCode = $this->pcRepository->getActivePromoCode($promoCode);

        if (null == $activePromoCode) {
            echo "1";
            return false;
        }

        // check if code is valid
        $promoCodeInfo = $activePromoCode->getPromocodesinfo();
        if (null != $promoCodeInfo->getValidfrom()) {
            if ($promoCodeInfo->getValidfrom() > new \DateTime()) {
                echo "2";
                return false;
            }
        }
        if (null != $promoCodeInfo->getValidto()) {
            if ($promoCodeInfo->getValidto() < new \DateTime()) {
                echo "3";
                return false;
            }
        }

        return true;

    }

}
