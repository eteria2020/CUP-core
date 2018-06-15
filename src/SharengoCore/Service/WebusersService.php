<?php

namespace SharengoCore\Service;

// Internals
use SharengoCore\Entity\Repository\WebusersRepository;
// Externals
use Doctrine\ORM\EntityManager;

class WebusersService
{
    /**
     * @var EntityManager
     */
    private $entityManager;
    
    /**
     * @var WebusersRepository
     */
    private $webusersRepository;

    /**
     * @param WebusersRepository $webusersRepository
     * @param EntityManager $entityManager
     * 
     */
    public function __construct(
    EntityManager $entityManager,
    $webusersRepository
    ) {
        $this->entityManager = $entityManager;
        $this->webusersRepository = $webusersRepository;
    }

    public function findById($id) {
        return $this->webusersRepository->findById($id)[0];
    }

    public function findByEmail($email) {
        return $this->webusersRepository->findByEmail($email)[0];
    }

}
