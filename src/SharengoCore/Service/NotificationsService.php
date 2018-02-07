<?php

namespace SharengoCore\Service;

// Internals
use SharengoCore\Entity\Notifications;
use SharengoCore\Entity\Repository\NotificationsRepository;
// Externals
use Doctrine\ORM\EntityManager;
use DateTime;
use Zend\Authentication\AuthenticationService as UserService;

class NotificationsService {

    private $entityManager;

    /**
     * @var UserService
     */
    private $userService;
    
    /**
     * @var CustomersService
     */
    private $customerService;

    /**
     * @param EntityManager $entityManager
     * @param NotificationsRepository $notificationsRepository
     * @param DatatableServiceInterface $datatableService
     * @param UserService $userService
     * @param CustomersService $customerService
     */
    public function __construct(
    EntityManager $entityManager, NotificationsRepository $notificationsRepository, DatatableServiceInterface $datatableService, UserService $userService, CustomersService $customerService
    ) {
        $this->entityManager = $entityManager;
        $this->notificationsRepository = $notificationsRepository;
        $this->datatableService = $datatableService;
        $this->userService = $userService;
        $this->customerService = $customerService;
    }

    public function getTotalNotifications() {
        return $this->notificationsRepository->getTotalNotifications();
    }

    /**
     * @param integer $id
     * @return Notifications
     */
    public function getNotificationById($id) {
        return $this->notificationsRepository->findOneById($id);
    }

    public function getDataDataTable(array $filters = [], $count = false) {
        $notifications = $this->datatableService->getData('Notifications', $filters, $count);

        if ($count) {
            return $notifications;
        }

        return array_map(function (Notifications $notifications) {
            return [
                'e' => [
                    'id' => $notifications->getId(),
                    'submitDate' =>
                    ($notifications->getSubmitDate() instanceof DateTime) ?
                    $notifications->getSubmitDate()->getTimestamp() : null,
                    'sentDate' =>
                    ($notifications->getSentDate() instanceof DateTime) ?
                    $notifications->getSentDate()->getTimestamp() : null,
                    'acknowledgeDate' =>
                    ($notifications->getAcknowledgeDate() instanceof DateTime) ?
                    $notifications->getAcknowledgeDate()->getTimestamp() : null,
                    'webuser' => $notifications->getWebuser()
                ],
                't' => [
                    'carPlate' => $notifications->getMeta()['car_plate'],
                    'tripId' => $notifications->getMeta()['trip_id']
                ],
                'c' => $this->getCustomer($this->customerService->findById($notifications->getMeta()['customer_id']))
            ];
        }, $notifications);
    }

    /**
     * Sets the acknowledge to the actual datetime of a specified notification.
     * Return the set DateTime.
     *
     * @param Notifications $notification
     * @param DateTime $acknolageDate
     */
    public function acknowledge(Notifications $notification, DateTime $acknolageDate) {
        $notification->setAcknowledgeDate($acknolageDate);

        // persist and flush notification
        $this->entityManager->persist($notification);
        $this->entityManager->flush();
    }

    public function webuser(Notifications $notification) {
        $notification->setWebuser($this->userService->getIdentity());

        // persist and flush notification
        $this->entityManager->persist($notification);
        $this->entityManager->flush();
    }

    private function getCustomer($customer) {
        if (isset($customer))
            return [
                'id' => $customer->getId(),
                'nameSurname' => $customer->getSurname() . " " . $customer->getName(),
                'mobile' => $customer->getMobile()
            ];
        else
            return [
                'id' => null,
                'nameSurname' => null,
                'mobile' => null
            ];
    }

}
