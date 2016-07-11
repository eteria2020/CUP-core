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
     * @param RenameUpload $renameUpload
     * @param array $config
     * @param EntityManager $entityManager
     * @param EventManager $eventManager
     * @param DatatableServiceInterface $datatableService
     */
    public function __construct(
        RenameUpload $renameUpload,
        array $config,
        EntityManager $entityManager,
        EventManager $eventManager,
        DatatableServiceInterface $datatableService
    ) {
        $this->renameUpload = $renameUpload;
        $this->config = $config;
        $this->entityManager = $entityManager;
        $this->eventManager = $eventManager;
        $this->datatableService = $datatableService;
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
                    'customerName' => $driversLicense->customerName(),
                    'customerSurname' => $driversLicense->customerSurname(),
                    'customerAddress' => $driversLicense->customerAddress(),
                    'customerBirthDate' => $driversLicense->customerBirthDate()->format('Y-m-d'),
                    'customerBirthPlace' => $driversLicense->customerBirthPlace(),
                    'driversLicenseNumber' => $driversLicense->driversLicenseNumber(),
                    'driversLicenseAuthority' => $driversLicense->driversLicenseAuthority(),
                    'driversLicenseCountry' => $driversLicense->driversLicenseCountry(),
                    'driversLicenseReleaseDate' => $driversLicense->driversLicenseReleaseDate()->format('Y-m-d'),
                    'driversLicenseName' => $driversLicense->driversLicenseName(),
                    'driversLicenseCategories' => $driversLicense->driversLicenseCategories(),
                    'driversLicenseExpire' => $driversLicense->driversLicenseExpire()->format('Y-m-d'),
                    'valid' => $driversLicense->valid(),
                    'first_time' => $driversLicense->isFirstTime()
                ],
                'cu' => [
                    'id' => $driversLicense->customerId(),
                    'email' => $driversLicense->getCustomerEmail(),
                    'mobile' => $driversLicense->getCustomerMobile(),
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
