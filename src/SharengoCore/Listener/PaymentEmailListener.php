<?php

namespace SharengoCore\Listener;

use SharengoCore\Service\EmailService;
use SharengoCore\Entity\Customers;

use Zend\EventManager\SharedListenerAggregateInterface;
use Zend\EventManager\SharedEventManagerInterface;
use Zend\EventManager\EventInterface;

final class PaymentEmailListener implements SharedListenerAggregateInterface
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
            'wrongTripPayment',
            [$this, 'registerWrongTripPaymentForCustomer']
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

    public function registerWrongTripPaymentForCustomer(EventInterface $e)
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
        $avoidEmails = $e->getParams()['avoidEmails'];

        if (!$avoidEmails) {
            foreach ($this->tripPaymentsByCustomer as $customerTrips) {
                $this->notifyCustomerOfWrongPayment($customerTrips['customer']);
            }
        }
    }

    /**
     * @param Customers $customer
     */
    private function notifyCustomerOfWrongPayment(Customers $customer)
    {
        $mail = $this->emailService->getMail(5, $customer->getLanguage());
        $content = sprintf(
            $mail->getContent(),
            $customer->getName()
        );
        
        //$customer->getSurname()
        //file_get_contents(__DIR__.'/../../../view/emails/wrong-payment-it_IT.html'),
        
        $attachments = [
            //'bannerphono.jpg' => $this->url . '/assets-modules/sharengo-core/images/bannerphono.jpg',
            //'barbarabacci.jpg' => $this->url . '/assets-modules/sharengo-core/images/barbarabacci.jpg'
        ];

        $this->emailService->sendEmail(
            $customer->getEmail(),
            $mail->getSubject(), //'SHARENGO - ERRORE NEL PAGAMENTO',
            $content,
            $attachments
        );
    }
}
