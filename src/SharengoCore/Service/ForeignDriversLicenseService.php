<?php

namespace SharengoCore\Service;

use SharengoCore\Form\DTO\UploadedFile;
use SharengoCore\Entity\Customers;
use SharengoCore\Entity\ForeignDriversLicenseUpload;

use Doctrine\ORM\EntityManager;
use Zend\Filter\File\RenameUpload;
use Zend\EventManager\EventManager;

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

    /**
     * @var EventManager
     */
    private $eventManager;

    public function __construct(
        RenameUpload $renameUpload,
        array $config,
        EntityManager $entityManager,
        EventManager $eventManager
    ) {
        $this->renameUpload = $renameUpload;
        $this->config = $config;
        $this->entityManager = $entityManager;
        $this->eventManager = $eventManager;
    }

    public function saveUploadedForeignDriversLicense(
        UploadedFile $uploadedFile,
        Customers $customer
    ) {
        $target = $this->config['path'] . '/foreign-drivers-license-' . $customer->getId();
        $this->renameUpload->setTarget($target);

        // we save the uploaded file in the file system
        $newFileLocation = $this->renameUpload->filter($uploadedFile->getTemporaryLocation());

        // we write the data of the customer and of the file in the database
        $foreignDriversLicenseUpload = new ForeignDriversLicenseUpload(
            $customer,
            $uploadedFile->getName(),
            $uploadedFile->getType(),
            $newFileLocation,
            $uploadedFile->getSize()
        );

        $this->entityManager->persist($foreignDriversLicenseUpload);
        $this->entityManager->flush();

        // we notify the application that the file is saved
        $this->eventManager->trigger('uploadedDriversLicense', $this, [
            'customer' => $customer
        ]);
    }
}
