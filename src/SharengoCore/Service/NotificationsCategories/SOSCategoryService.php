<?php

namespace SharengoCore\Service\NotificationsCategories;

// Internals
use SharengoCore\Entity\Notifications;
use SharengoCore\Entity\Customers;
use SharengoCore\Entity\Trips;
use SharengoCore\Service\CustomersService;
use SharengoCore\Service\TripsService;
use SharengoCore\Exception\CustomerNotFoundException;
use SharengoCore\Exception\TripNotFoundException;
use SharengoCore\Exception\MetadataNotValidException;
use SharengoCore\Service\NotificationsCategories\NotificationsCategoriesInterface;

class SOSCategoryService implements NotificationsCategoriesInterface
{
    /**
     * @var CustomersService
     */
    private $customersService;

    /**
     * @var TripsService
     */
    private $tripsService;

    /**
     * @param CustomersService $customersService
     * @param TripsService $tripsService
     */
    public function __construct(
        CustomersService $customersService,
        TripsService $tripsService
    ) {
        $this->customersService = $customersService;
        $this->tripsService = $tripsService;
    }

    /**
     * Return an array containing all the partials useful data knowed by this service.
     *
     * @throws MetadataNotValidException
     * @throws CustomerNotFoundException
     * @throws TripNotFoundException
     * @return Trips
     */
    public function getData(Notifications $notification)
    {
        return [
            'customer' => $this::getCustomer($notification),
            'trip' => $this::getTrip($notification)
        ];
    }

    /**
     * Return a Customer instance from Notifications instance meta field.
     *
     * @throws MetadataNotValidException
     * @throws CustomerNotFoundException
     * @return Customers
     */
    public function getCustomer(Notifications $notification)
    {
        $notificationMetadata = $notification->getMeta();
        $customerId = intval($notificationMetadata['customer_id']);

        // Check for cast error
        if (!$customerId) {
            throw new MetadataNotValidException();
        }

        $customer = $this->customersService->findById($customerId);

        if (!$customer instanceof Customers) {
            throw new CustomerNotFoundException();
        }

        return $customer;
    }

    /**
     * Return a Trips instance from Notifications instance meta field.
     *
     * @throws MetadataNotValidException
     * @throws TripNotFoundException
     * @return Trips
     */
    public function getTrip(Notifications $notification)
    {
        $notificationMetadata = $notification->getMeta();
        $tripId = intval($notificationMetadata['trip_id']);

        // Check for cast error
        if (!$tripId) {
            //throw new MetadataNotValidException();
            //temporal fix csd-1835
            return null;
        }

        $trip = $this->tripsService->getById($tripId);

        if (!$trip instanceof Trips) {
            //throw new TripNotFoundException();
            //temporal fix csd-1835
            return null;
        }

        return $trip;
    }
}
