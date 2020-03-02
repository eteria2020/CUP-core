<?php

namespace SharengoCore\Service;

// Internals
use SharengoCore\Entity\Repository\DocumentsRepository;
// Externals
use Doctrine\ORM\EntityManager;

class DocumentsService
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var DocumentsRepository
     */
    private $documentsRepository;

    /**
     * @param DocumentsRepository $documentsRepository
     * @param EntityManager $entityManager
     *
     */
    public function __construct(
        EntityManager $entityManager,
        $documentsRepository
    )
    {
        $this->entityManager = $entityManager;
        $this->documentsRepository = $documentsRepository;
    }

    /**
     * @param $id
     * @return mixed
     */
    public function findById($id)
    {
        return $this->documentsRepository->findById($id);
    }

    /**
     * @param $key
     * @param $country
     * @return mixed
     */
    public function findByKeyAndCountry($key, $country = null)
    {
        return $this->documentsRepository->findByKeyAndCountry($key, $country);
    }


}
