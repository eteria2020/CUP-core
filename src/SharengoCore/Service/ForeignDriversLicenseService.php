<?php

namespace SharengoCore\Service;

use SharengoCore\Form\DTO\UploadedFile;
use SharengoCore\Entity\Customers;
use SharengoCore\Entity\ForeignDriversLicenseUpload;

use Doctrine\ORM\EntityManager;
use Zend\Filter\File\RenameUpload;

class ForeignDriversLicenseService
{
    /**
     * @var RenameUpload
     */
    private $renameUpload;

    /**
     * @var array
     */
    private $config;

    /**
     * @var array
     */
    private $entityManager;

    public function __construct(
        RenameUpload $renameUpload,
        array $config,
        EntityManager $entityManager
    ) {
        $this->renameUpload = $renameUpload;
        $this->config = $config;
        $this->entityManager = $entityManager;
    }

    public function saveUploadedForeignDriversLicense(
        UploadedFile $uploadedFile,
        Customers $customer
    ) {
        $target = $this->config['path'] . '/foreign-drivers-license-' . $customer->getId();
        $this->renameUpload->setTarget($target);

        $newFileLocation = $this->renameUpload->filter($uploadedFile->getTemporaryLocation());

        $foreignDriversLicenseUpload = new ForeignDriversLicenseUpload(
            $customer,
            $uploadedFile->getName(),
            $uploadedFile->getType(),
            $newFileLocation,
            $uploadedFile->getSize()
        );

        $this->entityManager->persist($foreignDriversLicenseUpload);
        $this->entityManager->flush();
    }
}
