<?php

namespace SharengoCore\Service;

// Internals
use SharengoCore\Entity\NotificationsCategories;
use SharengoCore\Entity\Repository\NotificationsCategoriesRepository;

class NotificationsCategoriesService
{
    /**
     * @var NotificationsCategoriesRepository
     */
    private $notificationsCategoriesRepository;

    /**
     * @param NotificationsCategoriesRepository $notificationsCategoriesRepository
     */
    public function __construct(
        NotificationsCategoriesRepository $notificationsCategoriesRepository
    ) {
        $this->notificationsCategoriesRepository = $notificationsCategoriesRepository;
    }

    /**
     * @return mixed
     */
    public function getListNotificationsCategories()
    {
        return $this->notificationsCategoriesRepository->findAll();
    }
}
