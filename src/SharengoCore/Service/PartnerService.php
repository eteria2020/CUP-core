<?php

namespace SharengoCore\Service;

use SharengoCore\Entity\Repository\CustomersRepository;
use SharengoCore\Entity\Customers;
use SharengoCore\Service\FleetService;
use Cartasi\Entity\Contracts;
use Cartasi\Entity\Transactions;

use Doctrine\ORM\EntityManager;

class PartnerService implements ValidatorServiceInterface
{

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var CustomersRepository
     */
    private $customersRepository;

    /*
     * @var FleetService
     */
    private $fleetService;

    public function __construct(
        EntityManager $entityManager,
        CustomersRepository $customersRepository,
        FleetService $fleetService
    ) {
        $this->entityManager = $entityManager;
        $this->customersRepository = $customersRepository;
        $this->fleetService = $fleetService;
    }

    public function findByEmail($email)
    {
        return $this->customersRepository->findByCI('email', $email);
    }

    public function partnerData($param){
        return $this->customersRepository->partnerData($param);
    }

    /**
     * Find a customer tha match email or tax code or driver license
     * @param type $email
     * @param type $taxCode
     * @param type $driverLicense
     * @return Customers
     */
    public function findCustomerByMainFields($email, $taxCode, $driverLicense)
    {

        $customers = $this->customersRepository->findByCI("email", $email);
        if(!empty($customers)){
            return $customers[0];
        }

        $customers2 = $this->customersRepository->findByCI("taxCode", $taxCode);
        if(!empty($customers2)){
            return $customers2[0];
        }

        $customers3 = $this->customersRepository->findByCI("driverLicense", $driverLicense);
        if(!empty($customers3)){
            return $customers3[0];
        }

        return null;
    }

    /**
     * Insert a new customer
     * 
     * @param type $data
     * @return type
     * @throws \Exception
     */
    public function saveNewCustomer($data)
    {
        $result = null;

        $this->entityManager->getConnection()->beginTransaction();
        try {
            // set anagraphic data
            $customer = new Customers();
            $customer->setInsertedTs(date_create());
            //$customer->setPartner($data['partnerName']);
            $customer->setGender($data['gender']);
            $customer->setSurname($data['surname']);
            $customer->setName($data['name']);
            //$customer->setBirthDate(new \DateTime(sprintf('%s-%s-%s 00:00:00',$data['birthDate'][0], $data['birthDate'][1], $data['birthDate'][2])));
            $customer->setBirthDate(new \DateTime(sprintf('%s 00:00:00',implode('-', $data['birthDate']))));

            $customer->setBirthTown($data['birthTown']);
            $customer->setBirthProvince($data['birthProvince']);
            $customer->setBirthCountry($data['birthCountry']);

            $customer->setTaxCode($data['fiscalCode']);     //NB tax code
            $customer->setVat($data['vat']);
            $customer->setPhone($data['phone']);
            $customer->setMobile($data['mobile']);
            $customer->setFax('');
            $customer->setEmail($data['email']);
            $customer->setPassword($data['password']);

            $pins = ['primary' => $data['pin']];
            $customer->setPin(json_encode($pins));

            $customer->setAddress($data['address']['street']);
            $customer->setAddressInfo('');
            $customer->setTown($data['address']['town']);
            $customer->setZipCode($data['address']['zip']);
            $customer->setProvince($data['address']['province']);
            $customer->setCountry($data['address']['country']);

            $customer->setDriverLicense($data['drivingLicense']['number']);
            $customer->setDriverLicenseCountry($data['drivingLicense']['country']);
            $customer->setDriverLicenseAuthority($data['drivingLicense']['authority']);
            $customer->setDriverLicenseReleaseDate(new \DateTime(sprintf('%s 00:00:00',implode('-', $data['drivingLicense']['releaseDate']))));
            $customer->setDriverLicenseExpire(new \DateTime(sprintf('%s 00:00:00',implode('-', $data['drivingLicense']['expire']))));
            $customer->setDriverLicenseName($data['drivingLicense']['firstname']);
            $customer->setDriverLicenseSurname($data['drivingLicense']['surname']);
            $customer->setDriverLicenseCategories($data['drivingLicense']['category']);
            $customer->setDriverLicenseForeign($data['drivingLicense']['foreign']);

            $customer->setGeneralCondition1($data['generalCondition1']);
            $customer->setGeneralCondition2($data['generalCondition2']);
            $customer->setPrivacyCondition($data['privacyCondition']);
            $customer->setPrivacyInformation($data['privacyInformation']);

            // set backend data
            $hash = hash("MD5", strtoupper($data['email']).strtoupper($data['password']));
            $customer->setHash($hash);

            $customer->setEnabled(true);
            $customer->setFirstPaymentCompleted(true);
            $customer->setRegistrationCompleted(true);
            $customer->setDiscountRate(0);
            $customer->setPaymentAble(true);
            $customer->setFleet($this->fleetService->getFleetById(1));         // default Milano
            $customer->setLanguage('it');
            $customer->setMaintainer(false);
            $customer->setGoldList(false);

//            $customer->setProfilingCounter(0);
//            $customer->setReprofilingOption(0);

            $this->entityManager->persist($customer);
            $this->entityManager->flush();
            $this->entityManager->getConnection()->commit();

            //$result = $this->customersService->getUserFromHash($hash);  //TODO: improve
            $contract = $this->newContract($customer);
            $this->newTransaction($contract, 0, 'EUR', 'TPAY', 'TELEPASS+TPAY+PREPAID+-+-N', true);
            $result = $customer;

        } catch (\Exception $e) {
            $this->entityManager->getConnection()->rollback();
            var_dump($e);
            //throw $e;
        }

        return $result;
    }

    /**
     * Create a new contract
     * 
     * @param \SharengoCore\Service\Customers $customer
     * @return Contracts
     */
    private function newContract(Customers $customer) {

        $contract = new Contracts();
        $contract->setCustomer($customer);

        $this->entityManager->persist($contract);
        $this->entityManager->flush();

        return $contract;
    }

    /**
     * Create a new transiction.
     * 
     * @param Contracts $contract
     * @param int $amount
     * @param string $currency
     * @param string $brand
     * @param string $productType
     * @param bool $isFirstPayment
     * @return Transactions
     */
    private function newTransaction(Contracts $contract, $amount, $currency, $brand, $productType, $isFirstPayment) {

        $transaction = new Transactions();
        $transaction->setContract($contract);
        $transaction->setEmail($contract->getCustomer()->getEmail());
        $transaction->setAmount($amount);
        $transaction->setCurrency($currency);
        $transaction->setName($contract->getCustomer()->getName());
        $transaction->setSurname($contract->getCustomer()->getSurname());
        $transaction->setBrand($brand);

        $transaction->setOutcome('OK');
        $transaction->setDatetime(date_create());
        $transaction->setMessage('Message OK - subscription');
        $transaction->setRegion('EUROPE');
        $transaction->setCountry('ITA');
        $transaction->setProductType($productType);
        $transaction->setIsFirstPayment($isFirstPayment);

        $this->entityManager->persist($transaction);
        $this->entityManager->flush();

        return $transaction;
    }
}
