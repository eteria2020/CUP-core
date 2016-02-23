<?php

namespace SharengoCore\Service;

use SharengoCore\Form\DTO\UploadedFile;
use SharengoCore\Entity\Customers;
use SharengoCore\Entity\ForeignDriversLicenseUpload;
use SharengoCore\Entity\Repository\ForeignDriversLicenseUploadRepository;
use SharengoCore\Exception\ForeignDriversLicenseUploadNotFoundException;

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

    /**
     * @var DatatableService
     */
    private $datatableService;

    /**
     * @var ForeignDriversLicenseUploadRepository
     */
    private $foreignDriversLicenseUploadRepository;

    public function __construct(
        RenameUpload $renameUpload,
        array $config,
        EntityManager $entityManager,
        EventManager $eventManager,
        DatatableService $datatableService
    ) {
        $this->renameUpload = $renameUpload;
        $this->config = $config;
        $this->entityManager = $entityManager;
        $this->eventManager = $eventManager;
        $this->datatableService = $datatableService;
    }

    public function saveUploadedForeignDriversLicense(
        UploadedFile $uploadedFile,
        Customers $customer
    ) {
        $target = $this->config['path'] . '/foreign-drivers-license-' . $customer->getId();
        $this->renameUpload->setTarget($target);

        // we save the uploaded file in the file system
        $newFileLocation = $this->renameUpload->filter([
            'tmp_name' => $uploadedFile->getTemporaryLocation(),
            'name' => $uploadedFile->getName()
        ]);

        // we write the data of the customer and of the file in the database
        $foreignDriversLicenseUpload = new ForeignDriversLicenseUpload(
            $customer,
            $uploadedFile->getName(),
            $uploadedFile->getType(),
            $newFileLocation['tmp_name'],
            $uploadedFile->getSize()
        );

        $this->entityManager->persist($foreignDriversLicenseUpload);
        $this->entityManager->flush();

        // we notify the application that the file is saved
        $this->eventManager->trigger('uploadedDriversLicense', $this, [
            'customer' => $customer
        ]);
    }

    public function getDataDataTable(array $filters = [], $count = false)
    {
        $customers = $this->datatableService->getData('ForeignDriversLicenseUpload', $filters, $count);

        if ($count) {
            return $customers;
        }

        return array_map(function (ForeignDriversLicenseUpload $driversLicense) {
            return [
                'e' => [
                    'id' => $driversLicense->id(),
                    'customer' => $driversLicense->customerId(),
                    'customer_name' => $driversLicense->customerName(),
                    'customer_surname' => $driversLicense->customerSurname(),
                    'customer_address' => $driversLicense->customerAddress(),
                    'customer_birthdate' => $driversLicense->customerBirthDate()->format('Y-m-d'),
                    'customer_birthplace' => $driversLicense->customerBirthPlace(),
                    'drivers_license_number' => $driversLicense->driversLicenseNumber(),
                    'drivers_license_authority' => $driversLicense->driversLicenseAuthority(),
                    'drivers_license_country' => $driversLicense->driversLicenseCountry(),
                    'drivers_license_release_date' => $driversLicense->driversLicenseReleaseDate()->format('Y-m-d'),
                    'drivers_license_name' => $driversLicense->driversLicenseName(),
                    'drivers_license_categories' => $driversLicense->driversLicenseCategories(),
                    'drivers_license_expire' => $driversLicense->driversLicenseExpire()->format('Y-m-d'),
                    'valid' => $driversLicense->valid()
                ]
            ];
        }, $customers);
    }

    public function getTotalUploadedFiles()
    {
        return $this->getRepository()->totalUploadedFiles();
    }

    private function getRepository()
    {
        if (!$this->foreignDriversLicenseUploadRepository instanceof ForeignDriversLicenseUploadRepository) {
            $this->foreignDriversLicenseRepository =
                $this->entityManager->getRepository('\SharengoCore\Entity\ForeignDriversLicenseUpload');
        }

        return $this->foreignDriversLicenseRepository;
    }

    /**
     * @param int $id
     * @return ForeignDriversLicenseUpload
     * @throws ForeignDriversLicenseUploadNotFoundException
     */
    public function getUploadedFileById($id)
    {
        $uploadedFile = $this->getRepository()->findById($id);

        if (!$uploadedFile instanceof ForeignDriversLicenseUpload) {
            throw new ForeignDriversLicenseUploadNotFoundException();
        }

        return $uploadedFile;
    }
}
