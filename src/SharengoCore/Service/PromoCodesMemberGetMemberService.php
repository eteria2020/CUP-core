<?php

namespace SharengoCore\Service;

use Doctrine\ORM\EntityManager;
use SharengoCore\Service\CustomersService;
use SharengoCore\Entity\Customers;
use SharengoCore\Entity\CustomersBonus;
use SharengoCore\Entity\PromoCodes;
use SharengoCore\Entity\PromoCodesOnce;
use SharengoCore\Entity\Repository\PromoCodesInfoRepository;
use SharengoCore\Entity\Repository\PromoCodesOnceRepository;
use SharengoCore\Entity\Repository\PromoCodesRepository;

class PromoCodesMemberGetMemberService
{
    const SHARENGO_MGM = "SHARENGO_MGM";
    const SHARENGO_MGM_MAX_FOR_CUSTOMER_FATHER = 100;

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var CustomersService
     */
    private $customersService;

    /**
     * @var PromoCodesRepository
     */
    private $pcRepository;

    /**
     * @var PromoCodesInfoRepository
     */
    private $pcInfoRepository;

    /**
     * @var PromoCodesOnceRepository
     */
    private $pcOnceRepository;

    /**
     * @var PromoCodesService
     */
    private $pcService;
    /**
     * @param PromoCodesRepository $pcRepository
     * @param PromoCodesInfoRepository $pcInfoRepository
     */
    public function __construct(
        EntityManager $entityManager,
        CustomersService $customersService,
        PromoCodesRepository $pcRepository,
        PromoCodesInfoRepository $pcInfoRepository,
        PromoCodesOnceRepository $pcOnceRepository,
        PromoCodesService $pcService
    ) {
        $this->entityManager = $entityManager;
        $this->customersService = $customersService;
        $this->pcRepository = $pcRepository;
        $this->pcInfoRepository = $pcInfoRepository;
        $this->pcOnceRepository = $pcOnceRepository;
        $this->pcService = $pcService;
    }

    /**
     * Return true if $promoCode is SHARENGO_MGM_XXX, where XXX is a customer id.
     * The function check:
     * - customer id inside the promocode exist
     * - maximum number of code used
     * 
     * @param string $promoCode
     * @return boolean
     */
    public function isValid($promoCode) {

        if(preg_match('/^[A-Z0-9]{5}-[A-Z0-9]{5}$/', $promoCode)!=1) {     // pattern XXXXX-XXXXX
            return false;
        }

        $customer = $this->customersService->findByPromocodeMemberGetMember($promoCode);
        $test = $this->customersService->getPromocodeMemberGetMember($customer);

        if (is_null($customer)) {   // custumer id dosn't exist
            return false;
        }

        $promoCodesOnce = $this->pcOnceRepository->findByPromoCodeStartWith($promoCode . '-');
        if(count($promoCodesOnce)>self::SHARENGO_MGM_MAX_FOR_CUSTOMER_FATHER) { // reach maximum number of mgm promocodes
            return false;
        }

        if(!$this->checkValidity(self::SHARENGO_MGM.'_NEW')) {
            return false;
        }

        if(!$this->checkValidity(self::SHARENGO_MGM.'_OLD')) {
            return false;
        }

        return true;
    }

    /**
     * Check if the promo code is active and the promocode info's date are not expired.
     * 
     * @param string $promoCode
     * @return boolean
     */
    private function checkValidity($promoCode){

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

        return true;;
    }

    /**
     * @param string $promocode
     * @return PromoCodesOnce
     */
    public function getByPromoCode() {
        return $this->pcoRepository->findByPromoCode(self::SHARENGO_MGM);
    }

    /**
     * Return the old customer inside the string of promocode once.
     * I.e. promocodeOnce= 'D07D4-72E62-28824' return customer 22577
     * @param type $promoCodeOnce
     * @return type
     */
    private function getCustomerOldFromPromocodeOnce($promoCodeOnce) {
        $result = null;
        $pcoArray = explode("-", trim(strtoupper($promoCodeOnce)));

        if(count($pcoArray) == 3){
            if (is_numeric($pcoArray[2])) {
                $result = $this->customersService->findByPromocodeMemberGetMember($pcoArray[0].'-'.$pcoArray[1]);
            }
        }

        return $result;
    }

    /**
     * Return the promocode name widthout customer id
     * @param string $promoCode
     * @param boolean $flagNewCustomer
     * @return string
     */
    public function getPromoCodeNameWidthoutCustomerId($promoCode, $flagNewCustomer = true){
        $result = null;

        $customer = $this->customersService->findByPromocodeMemberGetMember($promoCode);
        if(!is_null($customer)) {
            if($flagNewCustomer) {
                $result= self::SHARENGO_MGM.'_NEW';
            }else {
                $result= self::SHARENGO_MGM.'_OLD';
            }
        }
        return $result;
    }

    /**
     * Create a new promocode once for the new customer (custome child).
     * The new pomocode once it's contatenation from promocode Member Get Member (XXXXX-XXXXX) and customer id of new customer.
     * This is because any promo code once must be unique.
     * 
     * @param string $promoCodeMgmName
     * @param Customers $customerNew
     * @return PromoCodesOnce
     */
    public function createPromoCodeOnceForNewCustomer($promoCodeMgmName, Customers $customerNew){
        $result = null;

        if($this->isValid($promoCodeMgmName)) {
            $customerOld = $this->customersService->findByPromocodeMemberGetMember($promoCodeMgmName);

            if(!is_null($customerOld)){
                $promoCodeFather = $this->pcRepository->getActivePromoCode(self::SHARENGO_MGM.'_NEW');
                $promoCodeInfo = $promoCodeFather->getPromocodesinfo();

                if(!is_null($promoCodeInfo)){
                    $promoCodeOnceName = $promoCodeMgmName.'-'.$customerNew->getId();
                    $result = new PromoCodesOnce($promoCodeInfo, $promoCodeOnceName);
                    $this->entityManager->persist($result);
                    $this->entityManager->flush();
                }
            }
        }

        return $result;
    }

    /**
     * Assign a bonus to an old customer that the new customer received a promocode once.
     * 
     * @param Customers $customerNew
     * @return CustomerBonus
     */
    public function assignBonusForOldCustomer(Customers $customerNew) {
        $result = null;
        $customerOld = null;

        $promoCodeOnces = $this->pcOnceRepository->findByPromoCodeByCustomer($customerNew);

        foreach($promoCodeOnces as $pco){   // find a promocode once for new customer
            $customerOld = $this->getCustomerOldFromPromocodeOnce($pco->getPromocode());
            if(!is_null($customerOld)){
                 break;
            }
        }

        if(!is_null($customerOld)){ // assign a bonus to old customer
            $promoCode = $this->pcService->getPromoCode(self::SHARENGO_MGM.'_OLD');
            if(!is_null($promoCode)){
                $customerBonus = CustomersBonus::createFromPromoCode($promoCode);
                $customerBonus->setCustomer($customerOld);
                $this->entityManager->persist($customerBonus);
                $this->entityManager->flush();
                $result = $customerBonus;
            }
        }

        return $result;
    }

}
