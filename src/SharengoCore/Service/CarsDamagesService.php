<?php

namespace SharengoCore\Service;

use Doctrine\ORM\EntityManager;

class CarsDamagesService
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /** @var CarsDamagesRepository */
    private $carsDamagesRepository;

    /**
     * @param EntityManager               $entityManager
     * @param UserServiceOptionsInterface $options
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->carsDamagesRepository = $this->entityManager->getRepository('\SharengoCore\Entity\CarsDamages');
    }

    public function getAll()
    {
        return $this->carsDamagesRepository->findAll();
    }
}
