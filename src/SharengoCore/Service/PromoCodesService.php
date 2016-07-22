<?php

namespace SharengoCore\Service;

use SharengoCore\Entity\Customers;
use SharengoCore\Entity\PromoCodes;
use SharengoCore\Entity\Repository\PromoCodesInfoRepository;
use SharengoCore\Entity\Repository\PromoCodesRepository;

class PromoCodesService
{
    /**
     * @var PromoCodesRepository
     */
    private $pcRepository;

    /**
     * @var PromoCodesInfoRepository
     */
    private $pcInfoRepository;

    /**
     * @param PromoCodesRepository $pcRepository
     * @param PromoCodesInfoRepository $pcInfoRepository
     */
    public function __construct(
        PromoCodesRepository $pcRepository,
        PromoCodesInfoRepository $pcInfoRepository
    ) {
        $this->pcRepository = $pcRepository;
        $this->pcInfoRepository = $pcInfoRepository;
    }

    /**
     * @param string $promoCode
     * @return PromoCodes
     */
    public function getPromoCode($promoCode) {
        return $this->pcRepository->findOneByPromocode($promoCode);
    }

    /**
     * @param string $promoCode
     * @return boolean
     */
    public function isValid($promoCode) {

        // get promocode from db
        $activePromoCode = $this->pcRepository->getActivePromoCode($promoCode);

        if (!$activePromoCode instanceof PromoCodes) {
            return false;
        }

        // check if code is valid
        $promoCodeInfo = $activePromoCode->getPromocodesinfo();
        if ($promoCodeInfo->getValidfrom() instanceof \DateTime) {
            if ($promoCodeInfo->getValidfrom() > date_create()) {
                return false;
            }
        }
        if ($promoCodeInfo->getValidto() instanceof \DateTime) {
            if ($promoCodeInfo->getValidto() < date_create()) {
                return false;
            }
        }

        return true;
    }

    /**
     * Returns true if code represents a standard PromoCode, false otherwise.
     * Standard PromoCodes are 4 to 6 characters long
     * i.e. Carrefour codes
     *
     * @param string $code
     * @return boolean
     */
    public function isStandardPromoCode($code)
    {
        return strlen($code) <= 6;
    }
}
