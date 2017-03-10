<?php

namespace SharengoCore\Service;

use SharengoCore\Listener\PaymentEmailListener;
use SharengoCore\Listener\NotifyCustomerPayListener;
use SharengoCore\Service\TripPaymentsService;
use SharengoCore\Service\CustomerDeactivationService;
use SharengoCore\Service\UsersService;
use SharengoCore\Service\CustomersService;

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

    /**
     * @var CustomersService
     */
    private  $customersService;

    public function __construct(
        EventManager $eventManager,
        LoggerInterface $logger,
        PaymentEmailListener $paymentEmailListener,
        NotifyCustomerPayListener $notifyCustomerPayListener,
        PaymentsService $paymentsService,
        TripPaymentsService $tripPaymentsService,
        CustomerDeactivationService $customerDeactivationService,
        UsersService $usersService,
        CustomersService $customersService
    ) {
        $this->eventManager = $eventManager;
        $this->logger = $logger;
        $this->paymentEmailListener = $paymentEmailListener;
        $this->notifyCustomerPayListener = $notifyCustomerPayListener;
        $this->paymentsService = $paymentsService;
        $this->tripPaymentsService = $tripPaymentsService;
        $this->customerDeactivationService = $customerDeactivationService;
        $this->usersService = $usersService;
        $this->customersService = $customersService;
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
                $this->logger->log( date_create()->format('H:i:s').";INF;processPayments;tripPayment->getId;".$tripPayment->getId() . "\n");
                $this->paymentsService->tryPayment(
                    $tripPayment,
                    $avoidEmails,
                    $avoidCartasi,
                    $avoidPersistance
                );
            } catch (WrongPaymentException $e) {
                $this->logger->log( date_create()->format('H:i:s').";ERR;processPayments;tripPayment->getId;".$tripPayment->getId() . "\n");
                $this->logger->log($e->getMessage(). "\n");
                // if we are not able to process a payment we skip the followings
                //break;
            }
        }

        $this->eventManager->trigger('processPaymentsCompleted', $this, [
            'avoidEmails' => $avoidEmails
        ]);
    }

    /*
     * After re-processing wrong payment we try to enabled the customer that was disabled.
     * To enable a customer we do:
     * - put true the enabled record
     * - put true the able_payment record
     * - set end_ts into customer_deactivations table
     */
   public function processCustomersDisabledAfterReProcess(
        $tripPayments,
        $avoidEmails = true,
        $avoidCartasi = true,
        $avoidPersistance = true
    ) {

        // extract list of customers belog of trip payments worng
        $arrayOfCustomers = array();
        foreach ($tripPayments as $tripPayment) {
            if (!array_key_exists( $tripPayment->getCustomer()->getId(), $arrayOfCustomers)) {
                $arrayOfCustomers[$tripPayment->getCustomer()->getId()]= $tripPayment->getCustomer();
            }
        }

        $this->logger->log(date_create()->format('H:i:s').";INF;processCustomersDisabledAfterReProcess;count(arrayOfCustomers);" . count($arrayOfCustomers) . "\n");
        foreach ($arrayOfCustomers as $customer) {
            //error_log(print_r("customer ".$customer->getId()." ". count($this->tripPaymentsService->getTripPaymentsWrong($customer, '-275 days')), TRUE));
            if(count($this->tripPaymentsService->getTripPaymentsWrong($customer, '-3 days'))===0){
                $this->logger->log(date_create()->format('H:i:s').";INF;processCustomersDisabledAfterReProcess;" . $customer->getId() . ";enabled\n");
                $webuser = $this->usersService->findUserById(12);
                $this->customersService->enableCustomerPayment($customer);
                $this->customerDeactivationService->reactivateCustomer($customer, $webuser, "customer enabled from retry wrong payments process", date_create());
                break; //TODO: only debug
            } else {
                $this->logger->log(date_create()->format('H:i:s').";INF;processCustomersDisabledAfterReProcess;" . $customer->getId() . ";stay disabled\n");
            }
        }
    }

    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
}