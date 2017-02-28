<?php

namespace SharengoCore\Service;

use SharengoCore\Listener\PaymentEmailListener;
use SharengoCore\Listener\NotifyCustomerPayListener;
use Cartasi\Exception\WrongPaymentException;

use Zend\EventManager\EventManager;

class ProcessPaymentsService
{
    /**
     * @var EventManager
     */
    private $eventManager;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var PaymentEmailListener
     */
    private $paymentEmailListener;

    /**
     * @var NotifyCustomerPayListener
     */
    private $notifyCustomerPayListener;

    /**
     * @var PaymentsService
     */
    private $paymentsService;

    public function __construct(
        EventManager $eventManager,
        LoggerInterface $logger,
        PaymentEmailListener $paymentEmailListener,
        NotifyCustomerPayListener $notifyCustomerPayListener,
        PaymentsService $paymentsService
    ) {
        $this->eventManager = $eventManager;
        $this->logger = $logger;
        $this->paymentEmailListener = $paymentEmailListener;
        $this->notifyCustomerPayListener = $notifyCustomerPayListener;
        $this->paymentsService = $paymentsService;
    }

    public function processPayments(
        $tripPayments,
        $avoidEmails = true,
        $avoidCartasi = true,
        $avoidPersistance = true
    ) {
        $this->eventManager->getSharedManager()->attachAggregate($this->paymentEmailListener);
        $this->eventManager->getSharedManager()->attachAggregate($this->notifyCustomerPayListener);

        foreach ($tripPayments as $tripPayment) {
            try {
                $this->logger->log( date_create()->format('H:i:s').";processing payment;".$tripPayment->getId() . "\n");
                $this->paymentsService->tryPayment(
                    $tripPayment,
                    $avoidEmails,
                    $avoidCartasi,
                    $avoidPersistance
                );
            } catch (WrongPaymentException $e) {
                $this->logger->log( date_create()->format('H:i:s').";payment error;".$tripPayment->getId() . "\n");
                $this->logger->log($e->getMessage(). "\n");
                // if we are not able to process a payment we skip the followings
                //break;
            }
        }

        $this->eventManager->trigger('processPaymentsCompleted', $this, [
            'avoidEmails' => $avoidEmails
        ]);
    }

    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }


}
