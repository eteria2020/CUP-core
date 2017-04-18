<?php

namespace SharengoCore\Listener;

use SharengoCore\Entity\Customers;

use Zend\EventManager\SharedListenerAggregateInterface;
use Zend\EventManager\SharedEventManagerInterface;
use Zend\EventManager\EventInterface;

final class DisableContractListener implements SharedListenerAggregateInterface
{
    /**
     * @var array
     */
    private $listeners = [];

    /**
     * @var string
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
            'DisableContractService',
            'disabledContract',
            [$this, 'disabledContract']
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

    public function disabledContract(EventInterface $e)
    {
        $contract = $e->getParams()['contract'];

        $this->sendCustomerNotification($contract->getCustomer());
    }

    private function sendCustomerNotification(Customers $customer)
    {
        // TODO: FIX THESE DATA 
        $this->emailService->getMail(6, $customer->getLanguage());
        $content = sprintf(
            $mail->getContent(),
            $customer->getName() 
        );
        //file_get_contents(__DIR__.'/../../../view/emails/disabled_contract_it-IT.html'),
        //$customer->getSurname()
        $attachments = [
            //'bannerphono.jpg' => $this->url . '/assets-modules/sharengo-core/images/bannerphono.jpg',
            //'barbarabacci.jpg' => $this->url . '/assets-modules/sharengo-core/images/barbarabacci.jpg'
        ];

        $this->emailService->sendEmail(
            $customer->getEmail(),
            $mail->getSubject(),//'SHARENGO - Disabilitazione carta di credito',
            $content,
            $attachments
        );
    }
}
