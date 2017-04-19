<?php

namespace SharengoCore\Listener;

use SharengoCore\Service\EmailService;
use SharengoCore\Entity\Customers;

use Zend\EventManager\SharedListenerAggregateInterface;
use Zend\EventManager\SharedEventManagerInterface;
use Zend\EventManager\EventInterface;

final class NotifyCustomerPayListener implements SharedListenerAggregateInterface
{
    /**
     * @var array
     */
    private $tripPaymentsByCustomer = [];

    /**
     * @var EmailService
     */
    private $emailService;

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
            'PaymentsService',
            'notifyCustomerPay',
            [$this, 'notifyCustomerPay']
        );

        $this->listeners[] = $events->attach(
            'ProcessPaymentsService',
            'processPaymentsCompleted',
            [$this, 'sendEmailToCustomers']
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

    public function notifyCustomerPay(EventInterface $e)
    {
        $params = $e->getParams();
        $customer = $params['customer'];
        $tripPayment = $params['tripPayment'];

        if (!isset($this->tripPaymentsByCustomer[$customer->getId()])) {
            $this->tripPaymentsByCustomer[$customer->getId()] = [
                'customer' => $customer,
                'tripPayments' => []
            ];
        }

        $tripPaymentsByCustomer[$customer->getId()]['tripPayments'][] = $tripPayment;
    }

    public function sendEmailToCustomers(EventInterface $e)
    {
        /*$avoidEmails = $e->getParams()['avoidEmails'];

        if (!$avoidEmails) {
            foreach ($this->tripPaymentsByCustomer as $customerTrips) {
                $this->notifyCustomerHeHasToPay($customerTrips['customer']);
            }
        }*/
    }

    /**
     * @param Customers $customer
     */
    private function notifyCustomerHeHasToPay(Customers $customer)
    {
        $date = date_create('midnight +7 days');
        $content = sprintf(
            file_get_contents(__DIR__.'/../../../view/emails/first-payment-request-it_IT.html'),
            $customer->getName(),
            $customer->getSurname(),
            $date->format('d/m/Y')
        );

        $attachments = [
            'bannerphono.jpg' => $this->url . '/assets-modules/sharengo-core/images/bannerphono.jpg',
            'barbarabacci.jpg' => $this->url . '/assets-modules/sharengo-core/images/barbarabacci.jpg'
        ];

        $this->emailService->sendEmail(
            $customer->getEmail(),
            'SHARENGO - Pagamento delle tue corse a debito',
            $content,
            $attachments
        );
    }
}
