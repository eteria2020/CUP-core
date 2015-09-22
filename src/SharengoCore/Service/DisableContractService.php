<?php

namespace SharengoCore\Service;

use Cartasi\Entity\Contracts;
use Cartasi\Service\CartasiContractsService;

use Doctrine\ORM\EntityManager;
use Zend\EventManager\EventManager;

class DisableContractService
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var CartasiContractsService
     */
    private $cartasiContractService;

    /**
     * @var TripPaymentsService
     */
    private $tripPaymentsService;

    /**
     * @var EventManager
     */
    private $eventManager;

    public function __construct(
        EntityManager $entityManager,
        CartasiContractsService $cartasiContractService,
        TripPaymentsService $tripPaymentsService,
        EventManager $eventManager
    ) {
        $this->entityManager = $entityManager;
        $this->cartasiContractService = $cartasiContractService;
        $this->tripPaymentsService = $tripPaymentsService;
        $this->eventManager = $eventManager;
    }

    /**
     * Sets now as the disabled date
     *
     * @param Contracts $contract
     */
    public function disableContract(Contracts $contract)
    {
        $this->entityManager->beginTransaction();

        try {
            $this->cartasiContractService->disableContract($contract);

            $this->tripPaymentsService->setWrongPaymentsAsToBePayed($contract->getCustomer());

            $this->entityManager->commit();
        } catch (\Exception $e) {
            $this->entityManager->rollback();
            throw $e;
        }

        $this->eventManager->trigger('disabledContract', $this, [
            'contract' => $contract
        ]);
    }
}
