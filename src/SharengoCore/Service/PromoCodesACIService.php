<?php

namespace SharengoCore\Service;

use Doctrine\ORM\EntityManager;
use SharengoCore\Service\CustomersService;
use SharengoCore\Service\PromoCodesService;
use SharengoCore\Service\PromoCodesOnceService;
use SharengoCore\Entity\Customers;
use SharengoCore\Entity\CustomersBonus;
use SharengoCore\Entity\PromoCodes;
use SharengoCore\Entity\Repository\PromoCodesInfoRepository;
use SharengoCore\Entity\Repository\PromoCodesRepository;
use SharengoCore\Entity\Repository\CustomersBonusRepository;

class PromoCodesACIService
{
    const SHARENGO_ACI = "SHARENGO_ACI";

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
     * @var CustomersBonusRepository
     */
    private $customerBonusRepository;

    /**
     * @var PromoCodesService
     */
    private $pcService;

    /**
     * @var array
     */
    private $config;

    /**
     * @param PromoCodesRepository $pcRepository
     * @param PromoCodesInfoRepository $pcInfoRepository
     */
    public function __construct(
        EntityManager $entityManager,
        CustomersService $customersService,
        PromoCodesRepository $pcRepository,
        PromoCodesInfoRepository $pcInfoRepository,
        PromoCodesService $pcService,
        array $config,
        CustomersBonusRepository $customerBonusRepository
    ) {
        $this->entityManager = $entityManager;
        $this->customersService = $customersService;
        $this->pcRepository = $pcRepository;
        $this->pcInfoRepository = $pcInfoRepository;
        $this->customerBonusRepository = $customerBonusRepository;
        $this->pcService = $pcService;
        $this->config = $config;
    }

    /**
     * Check ACI card validity.
     *
     * @param string $promoCode
     * @return boolean
     */
    public function isValid($promoCode) {
        $promoCode = strtoupper($promoCode);

        if(preg_match('/^[A-Z]{2}[0-9]{9}$/', $promoCode)!= 1) { // pattern XX000000000
            return false;
        }
        if(!$this->checkPromoValidity(self::SHARENGO_ACI)) {
            return false;
        }

        if($this->isThisCardUsed($promoCode)){
            return false;
        }
        //ACI API call
        return $this->checkACIValidity($promoCode);
    }

    /**
     * Check if the promo code is active and the promocode info's date are not expired.
     * 
     * @param string $promoCode
     * @return boolean
     */
    private function checkPromoValidity($promoCode){

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

    private function checkACIValidity($promoCode){
        if(is_null($this->config['username']) || is_null($this->config['password'] || is_null($this->config['verify_aci_card_url']) )){
            return false;
        }
        try {
            $client = new \SoapClient($this->config['verify_aci_card_url']);
            $params = ['sUser' => $this->config['username'], 'sPassword' => $this->config['password'], 'sNumeroTessera' => $promoCode];
            $response = $client->ricercaPosizioneSocio($params);
            return $this->handleAciResponse($response);
        }catch(\Error $e){
            return false;
        }
    }

    private function handleAciResponse($response){
        if ((is_null($response) || (isset($response->ricercaPosizioneSocioReturn) && is_null($response->ricercaPosizioneSocioReturn)))){
            return false;
        }
        try {
            $xml = simplexml_load_string($response->ricercaPosizioneSocioReturn);
            if(isset($xml->ESITO->CODICE_ESITO[0]) && $xml->ESITO->CODICE_ESITO[0] == 5){
                return true;
            } else {
                return false;
            }
        } catch(\Error $e){
            return false;
        }
    }

    /**
     * Assign a bonus to new customer
     * 
     * @param Customers $customerNew
     * @return PromoCodes
     */
    public function assingCustomerBonusForNewCustomer(Customers $customer, $card) {
        $result = $this->pcRepository->getActivePromoCode(self::SHARENGO_ACI);
        if(!is_null($result)) {
            $customerBonus = CustomersBonus::createFromPromoCode($result);
            $customerBonus->setDescription($customerBonus->getDescription().' - '.strtoupper($card));
            $customerBonus->setCustomer($customer);
            $customerBonus->setType($result->getPromocodesinfo()->getType());
            $this->entityManager->persist($customerBonus);
            $this->entityManager->flush();

            $customer->setDiscountRate($result->getPromoCodesInfo()->discountPercentage());
        }

        return $result;
    }

    private function isThisCardUsed($card){
        $cb = $this->customerBonusRepository->getBonusFromACICard($card);
        if(count($cb)>0){
            return true;
        } else {
            return false;
        }

    }

    /**
     * @param string $promocode
     * @return PromoCodes
     */
    public function getByPromoCode() {
        return $this->pcRepository->getActivePromoCode(self::SHARENGO_ACI);
    }

}
