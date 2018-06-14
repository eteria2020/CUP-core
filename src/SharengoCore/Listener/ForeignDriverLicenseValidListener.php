<?php

namespace SharengoCore\Listener;

use SharengoCore\Service\EmailService;
use SharengoCore\Entity\Customers;

use Zend\EventManager\SharedListenerAggregateInterface;
use Zend\EventManager\SharedEventManagerInterface;
use Zend\EventManager\EventInterface;

final class ForeignDriverLicenseValidListener implements SharedListenerAggregateInterface
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
     * @var
     */
    private $url;

    public function __construct($emailService, $url)
    {
        $this->emailService = $emailService;
        $this->url = $url;
    }

    public function attachShared(SharedEventManagerInterface $events)
    {
        $this->listeners[] = $events->attach(
            'ValidateForeignDriversLicenseService',
            'foreignDriversLicenseValidated',
            [$this, 'sendEmailToCustomer']
        );
    }

    public function detachShared(SharedEventManagerInterface $events)
    {
        foreach ($this->listeners as $index => $callback) {
            if ($events->detach($index, $callback)) {
                unset($this->listeners[$index]);
            }
        }
    }

    public function sendEmailToCustomer(EventInterface $e)
    {
        $customer = $e->getParam('customer');
        $mail = $this->emailService->getMail(2, $customer->getLanguage());
        $content = sprintf(
            $mail->getContent(),
            $customer->getName() 
        );
        //$customer->getSurname()
        //file_get_contents(__DIR__.'/../../../view/emails/foreign-driver-license-validated-it_IT.html'),
        $attachments = [
            //'bannerphono.jpg' => $this->url . '/assets-modules/sharengo-core/images/bannerphono.jpg',
            //'barbarabacci.jpg' => $this->url . '/assets-modules/sharengo-core/images/barbarabacci.jpg'
        ];

        $this->emailService->sendEmail(
            $customer->getEmail(),
            $mail->getSubject(), //'SHARENGO - La tua patente è stata validata',
            $content,
            $attachments
        );
    }
}
