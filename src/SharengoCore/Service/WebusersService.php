<?php

namespace SharengoCore\Service;

// Internals
use SharengoCore\Entity\Webuser;
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
     * @param EntityManager $entityManager
     */
    public function __construct(
        EntityManager $entityManager
    ) {
        $this->entityManager = $entityManager;
        $this->webusersRepository = $this->entityManager->getRepository('\SharengoCore\Entity\Webuser');
    }

    public function findById($id)
    {
        //return $this->webusersRepository->findById($id);
        return $this->webusersRepository->findById(96);
    }
    
}
