<?php

namespace SharengoCore\Service;

use SharengoCore\Entity\Vat;
use Doctrine\ORM\EntityManager;

class VatService {
    /**
     * @var EntityManager $entityManager
     */
    private $entityManager;

    /**
     * @var VatRepository
     */
    private $vatRepository;
    
    /**
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager) {
        $this->entityManager = $entityManager;
        $this->vatRepository = $this->entityManager->getRepository('\SharengoCore\Entity\Vat');
    }

    /**
     * @param integer $id
     * @return Vat
     */
    public function findById($id)
    {
        return $this->vatRepository->findOneBy([
            'id' => $id
        ]);
    }
}