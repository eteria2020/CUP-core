<?php

namespace SharengoCore\Service;

use Cartasi\Service\CartasiContractsService;
use SharengoCore\Entity\Cards;
use SharengoCore\Entity\Customers;
use SharengoCore\Entity\CustomersBonus;
use SharengoCore\Entity\CustomersPoints;
use SharengoCore\Entity\PromoCodes;
use SharengoCore\Entity\Repository\CustomersBonusRepository;
use SharengoCore\Entity\Repository\CustomersPointsRepository;
use SharengoCore\Entity\Repository\CustomersRepository;
use SharengoCore\Exception\BonusAssignmentException;
use SharengoCore\Service\DatatableServiceInterface;
use SharengoCore\Service\SimpleLoggerService as Logger;
use SharengoCore\Service\TripPaymentsService;
use SharengoCore\Service\TripService;

use Doctrine\ORM\EntityManager;
use Zend\Authentication\AuthenticationService as UserService;
use Zend\Mvc\I18n\Translator;


class CustomersPointsService 
{


    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var CustomersRepository
     */
    private $customersRepository;

    /**
     * @var CustomersBonusRepository
     */
    private $customersBonusRepository;

    /**
     * @var CustomersPointsRepository
     */
    private $customersPointsRepository;

    /**
     * @param EntityManager $entityManager
     * @param UserService $userService
     * @param DatatableServiceInterface $datatableService
     * @param CardsService $cardsService
     * @param EmailService $emailService
     * @param Logger $logger
     * @param CartasiContractsService $cartasiContractsService
     * @param TripPaymentsService $tripPaymentsService
     * @param string $url
     */
    public function __construct(
        EntityManager $entityManager
    ) {
        $this->entityManager = $entityManager;
        $this->customersRepository = $this->entityManager->getRepository('\SharengoCore\Entity\Customers');
        $this->customersBonusRepository = $this->entityManager->getRepository('\SharengoCore\Entity\CustomersBonus');
        $this->customersPointsRepository = $this->entityManager->getRepository('\SharengoCore\Entity\CustomersPoints');
    }

    
    public function buyPacketPoints($customer) {
        return $this->customersPointsRepository->buyPacketPoints($customer);
    }
    
    public function getTotalPoints($customer_id) {
        return $this->customersPointsRepository->getTotalPoints($customer_id);
    }
    
    public function addCustomerPoint(CustomersPoints $customerPoint){
        
        $this->entityManager->persist($customerPoint);
        $this->entityManager->flush();
        
        return $customerPoint;
    } 
    
    public function setCustomerPointPackage(CustomersPoints $customerPoint, Customers $customer){
        
        $date = new \DateTime();
        $date2 = new \DateTime();
        $date2 = $date2->modify("+10 years");
        
        $customerPoint->setCustomer($customer);
        $customerPoint->setWebuser(null);
        $customerPoint->setActive(TRUE);
        $customerPoint->setInsertTs($date);
        $customerPoint->setUpdateTs($date);
        $customerPoint->setTotal(-1500);
        $customerPoint->setResidual(0);
        $customerPoint->setType("PacchettoPunti");
        $customerPoint->setValidFrom($date);
        $customerPoint->setDurationDays(null);
        $customerPoint->setValidTo($date2);
        $customerPoint->setDescription("Desc pacchetto punti");
        
        return $customerPoint;
    }

}
