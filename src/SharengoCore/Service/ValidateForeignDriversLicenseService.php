<?php

namespace SharengoCore\Service;

use SharengoCore\Entity\ForeignDriversLicenseUpload;
use SharengoCore\Entity\ForeignDriversLicenseValidation;
use SharengoCore\Entity\Webuser;

use Doctrine\ORM\EntityManager;
use Zend\EventManager\EventManager;

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

    /**
     * @var EventManager
     */
    private $eventManager;

    public function __construct(
        EventManager $eventManager,
        EntityManager $entityManager,
        CustomerDeactivationService $customerDeactivationService
    ) {
        $this->entityManager = $entityManager;
        $this->customerDeactivationService = $customerDeactivationService;
        $this->eventManager = $eventManager;
    }

    /**
     * @param ForeignDriversLicenseUpload $foreignDriversLicense
     * @param Webuser $webuser
     * @throws \Exception
     * @internal param ForeignDriversLicenseUpload $
     */
    public function validateForeignDriversLicense(
        ForeignDriversLicenseUpload $foreignDriversLicense,
        Webuser $webuser
    ) {
        $this->entityManager->beginTransaction();

        try {
            $validation = new ForeignDriversLicenseValidation($foreignDriversLicense);
            $validation->validate($webuser);

            $this->entityManager->persist($validation);
            $this->entityManager->flush();

            $this->customerDeactivationService->reactivateCustomerForDriversLicense(
                $foreignDriversLicense->customer(),
                date_create()
            );
            $this->entityManager->commit();

            // we notify the application that the driver license is valid
            $customer = $foreignDriversLicense->customer();
            $this->eventManager->trigger('foreignDriversLicenseValidated', $this, [
                'customer' => $customer
            ]);
        } catch (\Exception $e) {
            $this->entityManager->rollback();
            throw $e;
        }
    }

    public function revokeForeignDriversLicense(
        ForeignDriversLicenseUpload $foreignDriversLicense,
        Webuser $webuser
    ) {
        $this->entityManager->beginTransaction();

        try {
            $validation = $foreignDriversLicense->getValidationToRevoke();
            $validation->revoke($webuser);

            $this->entityManager->persist($validation);
            $this->entityManager->flush();

            $this->customerDeactivationService->deactivateForDriversLicense(
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
