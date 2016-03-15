<?php

namespace SharengoCore\Service;

use SharengoCore\Entity\ForeignDriversLicenseUpload;
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
            $foreignDriversLicense->validate($webuser);

            $this->entityManager->persist($foreignDriversLicense);
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
}
