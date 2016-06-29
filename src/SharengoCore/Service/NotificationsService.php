<?php

namespace SharengoCore\Service;

// Internals
use SharengoCore\Entity\Notifications;
use SharengoCore\Entity\Repository\NotificationsRepository;
// Externals
use Doctrine\ORM\EntityManager;
use DateTime;

class NotificationsService
{
    private $entityManager;

    /**
     * @param EntityManager $entityManager
     * @param NotificationsRepository $notificationsRepository
     * @param DatatableServiceInterface $datatableService
     */
    public function __construct(
        EntityManager $entityManager,
        NotificationsRepository $notificationsRepository,
        DatatableServiceInterface $datatableService
    ) {
        $this->entityManager = $entityManager;
        $this->notificationsRepository = $notificationsRepository;
        $this->datatableService = $datatableService;
    }

    public function getTotalNotifications()
    {
        return $this->notificationsRepository->getTotalNotifications();
    }

    /**
     * @param integer $id
     * @return Notifications
     */
    public function getNotificationById($id)
    {
        return $this->notificationsRepository->findOneById($id);
    }

	public function getDataDataTable(array $filters = [], $count = false)
    {
        $notifications = $this->datatableService->getData('Notifications', $filters, $count);

        if ($count) {
            return $notifications;
        }

        return array_map(function (Notifications $notifications) {
            return [
                'e' => [
                    'id' => $notifications->getId(),
                    'subject' => $notifications->getSubject(),
                    'submitDate' => ($notifications->getSubmitDate() instanceof DateTime) ? $notifications->getSubmitDate()->getTimestamp() : null,
                    'sentDate' => ($notifications->getSentDate() instanceof DateTime) ? $notifications->getSentDate()->getTimestamp() : null,
                    'acknowledgeDate' => ($notifications->getAcknowledgeDate() instanceof DateTime) ? $notifications->getAcknowledgeDate()->getTimestamp() : null,
                ],
                'nc' => [
                    'name' => $notifications->getCategoryName(),
                ],
                'np' => [
                    'name' => $notifications->getProtocolName(),
                ],
                'button' => $notifications->getId(),
            ];
        }, $notifications);
    }

    /**
     * Sets the acknowledge to the actual datetime of a specified notification.
     * Return the set DateTime.
     *
     * @param Notifications $notification
     * @return DateTime
     */
    public function acknowledge(Notifications $notification)
    {
        $now = date_create();
        $notification->setAcknowledgeDate($now);

        // persist and flush notification
        $this->entityManager->persist($notification);
        $this->entityManager->flush();

        return $now;
    }
}