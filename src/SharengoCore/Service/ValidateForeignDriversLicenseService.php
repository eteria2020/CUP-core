<?php

namespace SharengoCore\Service;

use SharengoCore\Entity\ForeignDriversLicenseUpload;
use SharengoCore\Entity\Webuser;

use Doctrine\ORM\EntityManager;

class ValidateForeignDriversLicenseService
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var CustomerDeactivationService
     */
    private $customerDeactivationService;

    public function __construct(
        EntityManager $entityManager,
        CustomerDeactivationService $customerDeactivationService
    ) {
        $this->entityManager = $entityManager;
        $this->customerDeactivationService = $customerDeactivationService;
    }

    /**
     * @var ForeignDriversLicenseUpload
     */
    public function validateForeignDriversLicense(
        ForeignDriversLicenseUpload $foreignDriversLicense,
        Webuser $webuser
    ) {
        $this->entityManager->beginTransaction();

        try {
            $foreignDriversLicense->validate($webuser);

            $this->entityManager->persist($foreignDriversLicense);
            $this->entityManager->flush();

            $this->customerDeactivationService->reactivateCustomerForDriversLicense(
                $foreignDriversLicense->customer(),
                date_create()
            );

            $this->entityManager->commit();
        } catch (\Exception $e) {
            $this->entityManager->rollback();
            throw $e;
        }
    }
}
