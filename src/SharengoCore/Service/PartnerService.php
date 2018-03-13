<?php

namespace SharengoCore\Service;

use SharengoCore\Entity\Repository\CustomersRepository;
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

    /**
     * @param EntityManager $entityManager
     */
    public function __construct(
      EntityManager $entityManager
    ) {
        $this->entityManager = $entityManager;
        $this->customersRepository = $this->entityManager->getRepository('\SharengoCore\Entity\Customers');
    }

    public function findByEmail($email)
    {
        return $this->customersRepository->findByCI('email', $email);
    }

    public function partnerData($param){
        return $this->customersRepository->partnerData($param);
    }

}
