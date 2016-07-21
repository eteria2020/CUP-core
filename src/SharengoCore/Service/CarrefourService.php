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
     * @param EntityManager $entityManager
     * @param CarrefourUsedCodeRepository $repository
     * @param mixed[] $pcConfig
     */
    public function __construct(
        EntityManager $entityManager,
        CarrefourUsedCodeRepository $repository,
        array $pcConfig
    ) {
        $this->entityManager = $entityManager;
        $this->repository = $repository;
        $this->pcConfig = $pcConfig;
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
     * Checks wether the code is a dynamically generated valid code.
     * (At the moment only Carrefour codes are available)
     *
     * @param Customers $customer
     * @param string $code
     * @return integer
     */
    public function addFromCode(Customers $customer, $code)
    {
        $this->checkCarrefourCode($code);

        return $this->add(
            $customer,
            $code
        );
    }

    /**
     * @param Customers $customer
     * @param CustomersBonus $customersBonus
     * @param string $code
     * @param boolean $saveToDb
     * @return CarrefourUsedCode
     */
    private function add(
        Customers $customer,
        $code,
        $saveToDb = true
    ) {
        $customersBonus = CustomersBonus::createBonus(
            $customer,
            $this->pcConfig['minutes'],
            $this->pcConfig['description'],
            $this->pcConfig['validFor']
        );

        $carrefourUsedCode = new CarrefourUsedCode(
            $customer,
            $customersBonus,
            $code
        );

        if ($saveToDb) {
            $this->entityManager->persist($carrefourUsedCode);
            $this->entityManager->persist($customersBonus);
            $this->entityManager->flush();
        }

        return $carrefourUsedCode;
    }

    /**
     * Maximum length is of 24 characters (also restricted in db).
     * Format: 0-AAAA-BBB-CCCC-dd-mm-yy (7 pieces divided by a - ).
     *
     * 1 Fixed number. This will always be a 0.
     * 2 Shop code. The list is in the config.
     * 3 Number of the cash register. Valid from 1 to 99.
     * 4 Number of the receipt. Valid from 0001 to 9999.
     * 5 Number of the day. Valid from 1 to 31.
     * 6-7 Month and year. The list is in the config.
     *
     * @param string $code
     * @throws NotAValidCodeException
     * @throws CodeAlreadyUsedException
     */
    private function checkCarrefourCode($code)
    {
        // Check if the code is a valid Carrefour code
        $pieces = explode('-', $code);
        if (count($pieces) == 7 &&
            $pieces[0] == '0' &&
            array_key_exists($pieces[1], $this->pcConfig['shops']) &&
            $pieces[2] >= 1 &&
            $pieces[2] <= 99 &&
            strlen($pieces[3]) == 4 &&
            intval($pieces[3]) >= 1 &&
            intval($pieces[3]) <= 9999 &&
            $pieces[4] >= 1 &&
            $pieces[4] <= 31 &&
            in_array($pieces[6] . $pieces[5], $this->pcConfig['dates'])) {

        } else {
            throw new NotAValidCodeException();
        }

        // Check if the code has already been used
        $carrefourUsedCode = $this->getByCode($code);
        if ($carrefourUsedCode instanceof CarrefourUsedCode) {
            throw new CodeAlreadyUsedException();
        }
    }
}
