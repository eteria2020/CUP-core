<?php

namespace SharengoCore\Service;

// Internals
use SharengoCore\Entity\NotificationsProtocols;
use SharengoCore\Entity\Repository\NotificationsProtocolsRepository;

class NotificationsProtocolsService
{
    /**
     * @var NotificationsProtocolsRepository
     */
    private $notificationsProtocolsRepository;

    /**
     * @param NotificationsProtocolsRepository $notificationsProtocolsRepository
     */
    public function __construct(
        NotificationsProtocolsRepository $notificationsProtocolsRepository
    ) {
        $this->notificationsProtocolsRepository = $notificationsProtocolsRepository;
    }

    /**
     * @return mixed
     */
    public function getListNotificationsProtocols()
    {
        return $this->notificationsProtocolsRepository->findAll();
    }
}
