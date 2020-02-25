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
    private $listeners = [];

    /**
     * @var array
     */
    private $tripPaymentsByCustomer = [];
    
    /**
     * @var array
     */
    private $extraPaymentsByCustomer = [];

    /**
     * @var EmailService
     */
    private $emailService;

    /**
     * NotifyCustomerPayListener constructor.
     * @param $emailService
     */
    public function __construct($emailService)
    {
        $this->emailService = $emailService;
    }

    /**
     * @param SharedEventManagerInterface $events
     */
    public function attachShared(SharedEventManagerInterface $events)
    {
        array_push(
            $this->listeners,
            $events->attach(
                'PaymentsService',
                'notifyCustomerPay',
                [$this, 'notifyCustomerPay']
        ));

        array_push($this->listeners,
            $events->attach(
            'PaymentsService',
            'notifyCustomerPayExtra',
            [$this, 'notifyCustomerPayExtra']
        ));

        array_push($this->listeners,
            $events->attach(
            'ProcessPaymentsService',
            'processPaymentsCompleted',
            [$this, 'sendEmailToCustomers']
        ));
    }

    /**
     * @param SharedEventManagerInterface $events
     */
    public function detachShared(SharedEventManagerInterface $events)
    {
        foreach ($this->listeners as $index => $callback) {
            if ($events->detach($callback)) {
                unset($this->listeners[$index]);
            }
        }
    }

    /**
     * @param EventInterface $e
     */
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

    /**
     * @param EventInterface $e
     */
    public function notifyCustomerPayExtra(EventInterface $e)
    {
        $params = $e->getParams();
        $customer = $params['customer'];
        $extraPayment = $params['extraPayment'];

        if (!isset($this->extraPaymentsByCustomer[$customer->getId()])) {
            $this->extraPaymentsByCustomer[$customer->getId()] = [
                'customer' => $customer,
                'extraPayment' => []
            ];
        }

        $this->extraPaymentsByCustomer[$customer->getId()]['extrasPayment'][] = $extraPayment;
    }

    /**
     * @param EventInterface $e
     */
    public function sendEmailToCustomers(EventInterface $e)
    {
        $avoidEmails = $e->getParams()['avoidEmails'];

        if (!$avoidEmails) {
            foreach ($this->tripPaymentsByCustomer as $customerTrips) {
                $this->notifyCustomerHeHasToPay($customerTrips['customer']);
            }
        }
    }

    /**
     * @param Customers $customer
     */
    private function notifyCustomerHeHasToPay(Customers $customer)
    {
        $mail = $this->emailService->getMail(5, $customer->getLanguage());
        $content = sprintf(
            $mail->getContent(),
            $customer->getName()
        );

        $attachments = [];

        $this->emailService->sendEmail(
            $customer->getEmail(),
            $mail->getSubject(), //'SHARENGO - ERRORE NEL PAGAMENTO',
            $content,
            $attachments
        );
    }
}
