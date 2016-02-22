<?php

namespace SharengoCore\Entity\Repository;

use Doctrine\ORM\EntityManager;

/**
 * ForeignDriversLicenseUploadRepository
 */
class ForeignDriversLicenseUploadRepository
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function findUploadedFiles()
    {
        $dql = 'SELECT f FROM \SharengoCore\Entity\ForeignDriversLicenseUpload f';

        $query = $this->entityManager->createQuery($dql);

        return $query->getResult();
    }

    public function totalUploadedFiles()
    {
        $dql = 'SELECT count(f.id) FROM \SharengoCore\Entity\ForeignDriversLicenseUpload f';

        $query = $this->entityManager->createQuery($dql);

        return $query->getSingleScalarResult();
    }

    public function findById($id)
    {
        $dql = 'SELECT f FROM \SharengoCore\Entity\ForeignDriversLicenseUpload f ' .
            'WHERE f.id = :id';

        $query = $this->entityManager->createQuery($dql);
        $query->setParameter('id', $id);

        return $query->getOneOrNullResult();
    }
}
