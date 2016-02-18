<?php

namespace SharengoCore\Listener;

use SharengoCore\Entity\Customers;
use SharengoCore\Service\EmailService;

use Zend\EventManager\SharedListenerAggregateInterface;
use Zend\EventManager\SharedEventManagerInterface;
use Zend\EventManager\EventInterface;

final class UploadedDriversLicenseMailSender implements SharedListenerAggregateInterface
{
    /**
     * @var array
     */
    private $listeners = [];

    /**
     * @var EmailService
     */
    private $emailService;

    /**
     * @var string
     */
    private $notifyTo;

    public function __construct(EmailService $emailService, $notifyTo)
    {
        $this->emailService = $emailService;
        $this->notifyTo = $notifyTo;
    }

    public function attachShared(SharedEventManagerInterface $events)
    {
        $this->listeners[] = $events->attach(
            'ForeignDriversLicenseService',
            'uploadedDriversLicense',
            [$this, 'uploadedDriversLicense']
        );
    }

    public function detachShared(SharedEventManagerInterface $events)
    {
        foreach ($this->listeners as $index => $callback) {
            if ($events->detach($callback)) {
                unset($this->listeners[$index]);
            }
        }
    }

    public function uploadedDriversLicense(EventInterface $e)
    {
        $customer = $e->getParam('customer');

        $this->sendUploadedDriversLicenseNotification($customer);
    }

    private function sendUploadedDriversLicenseNotification(Customers $customer)
    {
        $this->emailService->sendEmail(
            $this->notifyTo,
            'SHARENGO - nuovo upload patente straniera',
            'Il cliente ' . $customer->getName() . ' ' . $customer->getSurname() .
            ' ha effettuato il caricamento di una copia della sua patente ' .
            'straniera. E\' necessario procedere alla validazione manuale'
        );
    }
}
