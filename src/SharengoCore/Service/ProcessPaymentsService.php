<?php

namespace SharengoCore\Service;

use SharengoCore\Listener\PaymentEmailListener;
use SharengoCore\Listener\NotifyCustomerPayListener;
use SharengoCore\Service\TripPaymentsService;
use SharengoCore\Service\CustomerDeactivationService;
use SharengoCore\Service\UsersService;

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

    /**
     * @var TripPaymentsService
     */
    private $tripPaymentsService;

    /**
     * @var CustomerDeactivationService
     */
    private $customerDeactivationService;

    /**
     * @var UsersService
     */
    private  $usersService;

    public function __construct(
        EventManager $eventManager,
        LoggerInterface $logger,
        PaymentEmailListener $paymentEmailListener,
        NotifyCustomerPayListener $notifyCustomerPayListener,
        PaymentsService $paymentsService,
        TripPaymentsService $tripPaymentsService,
        CustomerDeactivationService $customerDeactivationService,
        UsersService $usersService
    ) {
        $this->eventManager = $eventManager;
        $this->logger = $logger;
        $this->paymentEmailListener = $paymentEmailListener;
        $this->notifyCustomerPayListener = $notifyCustomerPayListener;
        $this->paymentsService = $paymentsService;
        $this->tripPaymentsService = $tripPaymentsService;
        $this->customerDeactivationService = $customerDeactivationService;
        $this->usersService = $usersService;
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

   public function processCustomersEnabledAfterReProcess(
        $tripPayments,
        $avoidEmails = true,
        $avoidCartasi = true,
        $avoidPersistance = true
    ) {
        $this->eventManager->getSharedManager()->attachAggregate($this->paymentEmailListener);
        $this->eventManager->getSharedManager()->attachAggregate($this->notifyCustomerPayListener);

        // extract list of customers belog of trip payments worng
        $arrayOfCustomers = array();
        foreach ($tripPayments as $tripPayment) {
            if (!array_key_exists( $tripPayment->getCustomer()->getId(), $arrayOfCustomers)) {
                $arrayOfCustomers[$tripPayment->getCustomer()->getId()]= $tripPayment->getCustomer();
            }
        }

        foreach ($arrayOfCustomers as $customer) {
            //error_log(print_r("customer ".$customer->getId()." ". count($this->tripPaymentsService->getTripPaymentsWrong($customer, '-275 days')), TRUE));
            if(count($this->tripPaymentsService->getTripPaymentsWrong($customer, '-2 days'))===0){
                // if customer haven't wrong payments in last 2 days
                error_log(print_r("customer ".$customer->getId()." reactivate", TRUE));
                $webuser = $this->usersService->findUserById(12);
                $this->customerDeactivationService->reactivateCustomer($customer, $webuser, "reactivation from retry wrong payments", date_create());
                break; //TODO: only debug
            } else {
                error_log(print_r("customer ".$customer->getId()." stay deactivated", TRUE));
            }
        }
    }

    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }


}
