<?php

namespace SharengoCore\Service;

use Doctrine\ORM\EntityManager;
use SharengoCore\Entity\CarrefourUsedCode;
use SharengoCore\Entity\Customers;
use SharengoCore\Entity\CustomersBonus;
use SharengoCore\Entity\Repository\CarrefourUsedCodeRepository;
use SharengoCore\Exception\CodeAlreadyUsedException;
use SharengoCore\Exception\NotAValidCodeException;

class CarrefourService
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var CarrefourUsedCodeRepository
     */
    private $repository;

    /**
     * @var mixed[]
     */
    private $pcConfig;

    /**
     * @var mixed[]
     */
    private $pcMarketConfig;

    /**
     * @param EntityManager $entityManager
     * @param CarrefourUsedCodeRepository $repository
     * @param mixed[] $pcConfig
     */
    public function __construct(
        EntityManager $entityManager,
        CarrefourUsedCodeRepository $repository,
        array $pcConfig,
        array $pcMarketConfig
    ) {
        $this->entityManager = $entityManager;
        $this->repository = $repository;
        $this->pcConfig = $pcConfig;
        $this->pcMarketConfig = $pcMarketConfig;
    }

    /**
     * @param integer $id
     * @return CarrefourUsedCode|null
     */
    public function getById($id)
    {
        return $this->repository->findById($id);
    }

    /**
     * @param string $code
     * @return CarrefourUsedCode|null
     */
    public function getByCode($code)
    {
        return $this->repository->findOneByCode($code);
    }

    /**
     * Checks wether the code is a dynamically generated valid code and
     * generates a CarrefourUsedCode.
     *
     * @param Customers $customer
     * @param string $code
     * @return integer
     */
    public function addFromCode(Customers $customer, $code)
    {
        $code = $this->normalize($code);
        $this->checkCarrefourCode($code);

        return $this->add(
            $customer,
            $code
        );
    }

    /**
     * Maximum length is of 24 characters (also restricted in db).
     * Format: AAAA-BBB-CCCC-dd-mm-yy (6 pieces divided by a - ). It may also be
     * preceded by a 0-.
     *
     * 1 Shop code. The list is in the config.
     * 2 Number of the cash register. Valid from 1 to 99.
     * 3 Number of the receipt. Valid from 0001 to 9999.
     * 4 Number of the day. Valid from 1 to 31.
     * 5-6 Month and year. The list is in the config.
     *
     * @param string $code
     * @return boolean
     * @throws NotAValidCodeException
     * @throws CodeAlreadyUsedException
     */
    public function checkCarrefourCode($code)
    {
        $code = $this->normalize($code);
        if($code===null){
            throw new NotAValidCodeException();
        }
        // Check if the code is a valid Carrefour code
        $pieces = explode('-', $code);

        // Remove prefix if it's equal to 0
        if (count($pieces) == 7 && $pieces[0] == '0') {
            $pieces = array_splice($pieces, 1);
        }

        $shops_array = array_merge($this->pcConfig['shops'], $this->pcMarketConfig['shops']);

        if (count($pieces) == 6 &&
            array_key_exists($pieces[0], $shops_array) &&
            intval($pieces[1]) >= 1 &&
            intval($pieces[1]) <= 99 &&
            strlen($pieces[2]) == 4 &&
            intval($pieces[2]) >= 1 &&
            intval($pieces[2]) <= 9999 &&
            intval($pieces[3]) >= 1 &&
            intval($pieces[3]) <= 31 &&
            in_array($pieces[5] . $pieces[4], $this->pcConfig['dates'])) {
            // the format is correct, proceed

        } else {
            throw new NotAValidCodeException();
        }

        // Check if the code has already been used
        $carrefourUsedCode = $this->getByCode($code);
        if ($carrefourUsedCode instanceof CarrefourUsedCode) {
            throw new CodeAlreadyUsedException();
        }
    }

    /**
     * @param Customers $customer
     * @param CustomersBonus $customersBonus
     * @param string $code
     * @return CarrefourUsedCode
     */
    private function add(
        Customers $customer,
        $code
    ) {
        $code = $this->normalize($code);

        $minutes = $this->pcConfig['minutes'];
        $description = $this->pcConfig['description'];
        $validFor = $this->pcConfig['validFor'];

        if($this->isMarket($code)){
            $minutes = $this->pcMarketConfig['minutes'];
            $description = $this->pcMarketConfig['description'];
            $validFor = $this->pcMarketConfig['validFor'];
        }

        $customersBonus = CustomersBonus::createBonus(
            $customer,
            $minutes,
            $description,
            $validFor
        );
        $this->entityManager->persist($customersBonus);

        $carrefourUsedCode = new CarrefourUsedCode(
            $customer,
            $customersBonus,
            $code
        );
        $this->entityManager->persist($carrefourUsedCode);

        $this->entityManager->flush();

        return $carrefourUsedCode;
    }

    /**
     * @param string $code
     * @return CarrefourUsedCode
     */
    private function isMarket($code){
        $result = FALSE;

        try {
            $pieces = explode('-', $code);
            if(array_key_exists($pieces[0], $this->pcMarketConfig['shops'])) {
                $result = TRUE;
            }
        } catch (Exception $ex) {}

        return $result;
    }

    /**
     * @param string $code
     * @return normalize
     */
    private function normalize($code){
        $result = null;

        try {
            $pieces = explode('-', $code);
            if(count($pieces)==6){
                $pieces[0] = str_pad ($pieces[0], 4, "0", STR_PAD_LEFT);
                $pieces[1] = str_pad ($pieces[1], 3, "0", STR_PAD_LEFT);
                $pieces[2] = str_pad ($pieces[2], 4, "0", STR_PAD_LEFT);
                $pieces[3] = str_pad ($pieces[3], 2, "0", STR_PAD_LEFT);
                $pieces[4] = str_pad ($pieces[4], 2, "0", STR_PAD_LEFT);
                $pieces[5] = str_pad ($pieces[5], 2, "0", STR_PAD_LEFT);

                $result = $pieces[0] . '-' .
                        $pieces[1] . '-' .
                        $pieces[2] . '-' .
                        $pieces[3] . '-' .
                        $pieces[4] . '-' .
                        $pieces[5];
            }
        } catch (Exception $ex) {
            $result = null;
        }

        return $result;
    }

}
