<?php

namespace SharengoCore\Service;

// Internals
use SharengoCore\Form\DTO\UploadedFile;
use SharengoCore\Entity\Customers;
use SharengoCore\Entity\ForeignDriversLicenseUpload;
use SharengoCore\Entity\Repository\ForeignDriversLicenseUploadRepository;
use SharengoCore\Exception\ForeignDriversLicenseUploadNotFoundException;
// Externals
use Doctrine\ORM\EntityManager;
use Zend\Filter\File\RenameUpload;
use Zend\EventManager\EventManager;
use Zend\Session\Container;
use ZipArchive;

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
     * @var DatatableServiceInterface
     */
    private $datatableService;

    /**
     * @var ForeignDriversLicenseUploadRepository
     */
    private $foreignDriversLicenseUploadRepository;

    /**
     * @var Container
     */
    private $datatableFiltersSessionContainer;

    /**
     * @param RenameUpload $renameUpload
     * @param array $config
     * @param EntityManager $entityManager
     * @param EventManager $eventManager
     * @param DatatableServiceInterface $datatableService
     * @param Container $datatableFiltersSessionContainer
     */
    public function __construct(
        RenameUpload $renameUpload,
        array $config,
        EntityManager $entityManager,
        EventManager $eventManager,
        DatatableServiceInterface $datatableService,
        Container $datatableFiltersSessionContainer
    ) {
        $this->renameUpload = $renameUpload;
        $this->config = $config;
        $this->entityManager = $entityManager;
        $this->eventManager = $eventManager;
        $this->datatableService = $datatableService;
        $this->datatableFiltersSessionContainer = $datatableFiltersSessionContainer;
    }

    /**
     * @param array $uploadedFiles
     * @param Customers $customer
     */
    public function saveUploadedFiles(
        array $uploadedFiles,
        Customers $customer
    ) {
        $target = $this->config['path'] . '/foreign-drivers-license-' . $customer->getId();

        if (count($uploadedFiles) == 1) {
            $upload = $this->uploadSingleFile($customer, $uploadedFiles[0], $target);
        } else {
            $upload = $this->uploadMultipleFiles($customer, $uploadedFiles, $target);
        }

        $this->entityManager->persist($upload);
        $this->entityManager->flush();

        // we notify the application that the file is saved
        $this->eventManager->trigger('uploadedDriversLicense', $this, [
            'customer' => $customer
        ]);
    }

    private function uploadMultipleFiles($customer, $uploadedFiles, $target)
    {
        $destination = $target . '.zip';
        $zip = new \ZipArchive();
        $tmp_file = tempnam('.','');
        $zip->open($tmp_file, ZipArchive::CREATE);

        /** @var UploadedFile $uploadedFile */
        foreach ($uploadedFiles as $uploadedFile) {
            $zip->addFile($uploadedFile->getTemporaryLocation(), $uploadedFile->getName());
        }
        $zip->close();

        // we move the zip from /tmp to the right directory
        rename($tmp_file, $destination);

        return new ForeignDriversLicenseUpload(
            $customer,
            end(explode('/', $destination)),
            'application/zip',
            $destination,
            filesize($destination)
        );
    }

    private function uploadSingleFile($customer, $uploadedFile, $target)
    {
        // we save the uploaded file in the file system
        $this->renameUpload->setTarget($target);
        $newFileLocation = $this->renameUpload->filter([
            'tmp_name' => $uploadedFile->getTemporaryLocation(),
            'name' => $uploadedFile->getName()
        ]);

        // we write the data of the customer and of the file in the database
        return new ForeignDriversLicenseUpload(
            $customer,
            $uploadedFile->getName(),
            $uploadedFile->getType(),
            $newFileLocation['tmp_name'],
            $uploadedFile->getSize()
        );
    }

    /**
     * This method return an array containing the DataTable filters,
     * from a Session Container.
     *
     * @return array
     */
    public function getDataTableSessionFilters()
    {
        return $this->datatableFiltersSessionContainer->offsetGet('ForeignDriversLicenseUpload');
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
                    'valid' => $driversLicense->valid(),
                    'first_time' => $driversLicense->isFirstTime()
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
