<?php

namespace SharengoCore\Service;

use SharengoCore\Entity\ExtraPayments;
use SharengoCore\Listener\PaymentEmailListener;
use SharengoCore\Listener\NotifyCustomerPayListener;
use SharengoCore\Service\TripPaymentsService;
use SharengoCore\Service\ExtraPaymentsService;
use SharengoCore\Service\CustomerDeactivationService;
use SharengoCore\Service\UsersService;
use SharengoCore\Service\CustomersService;

use Cartasi\Exception\WrongPaymentException;

use Zend\EventManager\EventManager;

class ProcessExtraService
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
     * @var ExtraPaymentsService
     */
    private $extraPaymentsService;

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

    /**
     * ProcessExtraService constructor.
     *
     * @param EventManager $eventManager
     * @param LoggerInterface $logger
     * @param PaymentEmailListener $paymentEmailListener
     * @param NotifyCustomerPayListener $notifyCustomerPayListener
     * @param PaymentsService $paymentsService
     * @param \SharengoCore\Service\TripPaymentsService $tripPaymentsService
     * @param \SharengoCore\Service\ExtraPaymentsService $extraPaymentsService
     * @param \SharengoCore\Service\CustomerDeactivationService $customerDeactivationService
     * @param \SharengoCore\Service\UsersService $usersService
     * @param \SharengoCore\Service\CustomersService $customersService
     */
    public function __construct(
        EventManager $eventManager,
        LoggerInterface $logger,
        PaymentEmailListener $paymentEmailListener,
        NotifyCustomerPayListener $notifyCustomerPayListener,
        PaymentsService $paymentsService,
        TripPaymentsService $tripPaymentsService,
        ExtraPaymentsService $extraPaymentsService,
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
        $this->extraPaymentsService = $extraPaymentsService;
        $this->customerDeactivationService = $customerDeactivationService;
        $this->usersService = $usersService;
        $this->customersService = $customersService;
    }

    /**
     * @param $extraPayments
     * @param bool $avoidEmails
     * @param bool $avoidCartasi
     * @param bool $avoidPersistance
     */
    public function processPayments(
        $extraPayments,
        $avoidEmails = true,
        $avoidCartasi = true,
        $avoidPersistance = true
    ) {
        //$this->eventManager->getSharedManager()->attachAggregate($this->paymentEmailListener);
        //$this->eventManager->getSharedManager()->attachAggregate($this->notifyCustomerPayListener);

        foreach ($extraPayments as $extraPayment) {
            try {

                $this->paymentsService->tryPaymentExtra(
                    $extraPayment,
                    $avoidEmails,
                    $avoidCartasi,
                    $avoidPersistance
                );

                $this->logger->log(sprintf("%s;INF;processPayments;c;%s;e;%s;%s;%s;%s\n",
                    date_create()->format('y-m-d H:i:s'),
                    $extraPayment->getCustomer()->getId(),
                    $extraPayment->getId(),
                    $extraPayment->getPaymentType(),
                    $extraPayment->getAmount(),
                    $extraPayment->getStatus()
                ));

            } catch(\Doctrine\ORM\ORMException $de){
                $this->logger->log( date_create()->format('y-m-d H:i:s').";ERR;processPayments;doctrine exception;ExtraPayment->getId;".$extraPayment->getId() . "\n");
                $this->logger->log($de->getMessage() . " " . $de->getFile() . " line " . $de->getLine() . "\n");
                $this->logger->log($de->getTraceAsString(). "\n");
                // \Doctrine\Common\Util\Debug::dump($tripPayment);
                break;
            } catch (\Exception $e) {
                $this->logger->log( date_create()->format('y-m-d H:i:s').";ERR;processPayments;general exception;ExtraPayment->getId;".$extraPayment->getId() . "\n");
                $this->logger->log($e->getMessage() . " " . $e->getFile() . " line " . $e->getLine() . "\n");
                $this->logger->log($e->getTraceAsString(). "\n");
                // \Doctrine\Common\Util\Debug::dump($tripPayment);
                if(strpos($e->getMessage(), "An exception occurred while executing 'INSERT INTO ")!==false ){
                    break;
                }
                // if we are not able to process a payment we skip the followings
                //break;
            }
        }

        /*$this->eventManager->trigger('processPaymentsCompleted', $this, [
            'avoidEmails' => $avoidEmails
        ]);*/
    }

    /**
     * @param bool $avoidEmails
     */
    public function processPaymentsCompleted($avoidEmails = true){

        $this->eventManager->getSharedManager()->attachAggregate($this->paymentEmailListener);
        $this->eventManager->getSharedManager()->attachAggregate($this->notifyCustomerPayListener);

        $this->eventManager->trigger('processPaymentsCompleted', $this, [
            'avoidEmails' => $avoidEmails
        ]);

    }

    /**
     * After re-processing wrong payment we try to enabled the customer that was disabled.
     * To enable a customer we do:
     * - put true the enabled record
     * - put true the able_payment record
     * - set end_ts into customer_deactivations table
     *
     * @param $extraPayments
     * @param bool $avoidEmails
     * @param bool $avoidCartasi
     * @param bool $avoidPersistance
     * @param null $timestampEndParam
     */
   public function processCustomersDisabledAfterReProcess(
        $extraPayments,
        $avoidEmails = true,
        $avoidCartasi = true,
        $avoidPersistance = true,
        $timestampEndParam = null
    ) {

        // extract list of customers belog of extra payments worng
        $arrayOfCustomers = array();
        foreach ($extraPayments as $extraPayment) {
            if (!array_key_exists($extraPayment->getCustomer()->getId(), $arrayOfCustomers)) {
                $arrayOfCustomers[$extraPayment->getCustomer()->getId()] = $extraPayment->getCustomer();
            }
        }

        $this->logger->log(date_create()->format('y-m-d H:i:s').";INF;processCustomersDisabledAfterReProcess;count(arrayOfCustomers);" . count($arrayOfCustomers) . "\n");
        foreach ($arrayOfCustomers as $customer) {
            
            if(count($this->extraPaymentsService->getExtraPaymentsWrong($customer, $timestampEndParam))===0){
                $this->logger->log(date_create()->format('y-m-d H:i:s').";INF;processCustomersDisabledAfterReProcess;" . $customer->getId() . ";enabled\n");
                $webuser = $this->usersService->findUserById(12);
                $this->customersService->enableCustomerPayment($customer);
                $this->customerDeactivationService->reactivateCustomer($customer, $webuser, "customer enabled from retry wrong payments process", date_create());
            } else {
                $this->logger->log(date_create()->format('y-m-d H:i:s').";INF;processCustomersDisabledAfterReProcess;" . $customer->getId() . ";stay disabled\n");
            }
        }
    }

    /**
     * @param LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
}