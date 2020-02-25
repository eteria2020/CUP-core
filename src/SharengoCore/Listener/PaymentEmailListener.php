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
    private $listeners = [];

    /**
     * @var array
     */
    private $tripPaymentsByCustomer = [];

    /**
     * @var EmailService
     */
    private $emailService;


    /**
     * PaymentEmailListener constructor.
     * @param EmailService $emailService
     */
    public function __construct(EmailService $emailService)
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
            'wrongTripPayment',
            [$this, 'registerWrongTripPaymentForCustomer']
        ));

        array_push(
        $this->listeners,
            $events->attach(
            'PaymentsService',
            'wrongExtraPayment',
            [$this, 'registerWrongExtraPaymentForCustomer']
        ));

        array_push(
            $this->listeners,
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

        $this->tripPaymentsByCustomer[$customer->getId()]['tripPayments'][] = $tripPayment;
    }

    /**
     * @param EventInterface $e
     */
    public function registerWrongExtraPaymentForCustomer(EventInterface $e)
    {
        $params = $e->getParams();
        $customer = $params['customer'];
//        $extraPayment = $params['extraPayment'];

        if (!isset($this->tripPaymentsByCustomer[$customer->getId()])) {
            $this->tripPaymentsByCustomer[$customer->getId()] = [
                'customer' => $customer,
                'extraPayments' => []
            ];
        }

//        $extraPaymentsByCustomer[$customer->getId()]['extraPayments'][] = $extraPayment;
    }

    /**
     * @param EventInterface $e
     */
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

        $attachments = [];

        $this->emailService->sendEmail(
            $customer->getEmail(),
            $mail->getSubject(), //'SHARENGO - ERRORE NEL PAGAMENTO',
            $content,
            $attachments
        );
    }
}
