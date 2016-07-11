<?php

namespace SharengoCore\Service\NotificationsCategories;

// Internals
use SharengoCore\Entity\Notifications;

interface NotificationsCategoriesInterface
{
    /**
     * Return an array containing all the partials useful data.
     *
     * @param Notification $notification
     * @return mixed[]
     */
    public function getData(Notifications $notification);
}
