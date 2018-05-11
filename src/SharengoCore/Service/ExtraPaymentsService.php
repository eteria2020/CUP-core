<?php

namespace SharengoCore\Service;

use SharengoCore\Entity\Repository\ExtraPaymentsRepository;
use SharengoCore\Entity\ExtraPayments;
use SharengoCore\Entity\ExtraPaymentsCanceled;
use SharengoCore\Entity\ExtraPaymentTriesCanceled;
use SharengoCore\Service\ExtraPaymentTriesService;
use SharengoCore\Service\CustomersService;
use SharengoCore\Entity\Customers;
use SharengoCore\Entity\Webuser;
use SharengoCore\Service\InvoicesService;
use SharengoCore\Entity\Fleet;
use SharengoCore\Entity\Queries\AllExtraPaymentTypes;
use Cartasi\Entity\Transactions;
use SharengoCore\Service\DatatableServiceInterface;

use Doctrine\ORM\EntityManager;

class ExtraPaymentsService
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var InvoicesService
     */
    private $invoicesService;
    
    /**
     * @var DatatableServiceInterface
     */
    private $datatableService;
    
    /**
     * @var ExtraPaymentsRepository
     */
    private $extraPaymentsRepository;
    
    /**
     * @var ExtraPaymentTriesService
     */
    private $extraPaymentTriesService;
    
    /**
     * @var CustomersService
     */
    private $customersService;

    /**
     * @param EntityManager $entityManager
     * @param InvoicesService $invoicesService
     * @param DatatableServiceInterface $datatableService
     * @param ExtraPaymentsRepository $extraPaymentsRepository
     * @param CustomersService $customersService
     */
    public function __construct(
        EntityManager $entityManager,
        InvoicesService $invoicesService,
        DatatableServiceInterface $datatableService,
        ExtraPaymentsRepository $extraPaymentsRepository,
        ExtraPaymentTriesService $extraPaymentTriesService,
        CustomersService $customersService
    ) {
        $this->entityManager = $entityManager;
        $this->invoicesService = $invoicesService;
        $this->datatableService = $datatableService;
        $this->extraPaymentsRepository = $extraPaymentsRepository;
        $this->extraPaymentTriesService = $extraPaymentTriesService;
        $this->customersService = $customersService;
    }

    /**
     * @param Customers $customer
     * @param Fleet $fleet
     * @param Transactions $transaction
     * @param int $amount
     * @param string $type
     * @param string[] $penalty
     * @param string[] $reasons
     * @param integer[] $amounts
     * @return ExtraPayment
     */
    public function registerExtraPayment(
        Customers $customer,
        Fleet $fleet,
        //Transactions $transaction,
        $transaction,
        $amount,
        $type,
        $penalty,
        $reasons,
        $amounts
    ) {
        $reasonsAmounts = [];
        if($type === "extra"){
            for ($i = 0; $i < count($reasons); $i++) {
                array_push(
                    $reasonsAmounts,
                    [[$reasons[$i]], [$this->formatAmount($amounts[$i])]]
                );
            }
        }else{
            for ($i = 0; $i < count($reasons); $i++) {
                array_push(
                    $reasonsAmounts,
                    [[$penalty[$i]], [$reasons[$i]], [$this->formatAmount($amounts[$i])]]
                );
            }
        }

        $extraPayment = new ExtraPayments(
            $customer,
            $fleet,
            $transaction,
            $amount,
            $type,
            $reasonsAmounts
        );

        $this->entityManager->persist($extraPayment);
        $this->entityManager->flush();

        return $extraPayment;
    }

    /**
     * @param ExtraPayment $extraPayment
     * @param bool $doFlush
     */
    public function generateInvoice(
        ExtraPayment $extraPayment,
        $doCommit = true
    ) {
        $this->entityManager->beginTransaction();

        try {
            // create the invoice
            $invoice = $this->invoicesService->prepareInvoiceForExtraOrPenalty(
                $extraPayment->getCustomer(),
                $extraPayment->getFleet(),
                $extraPayment->getReasons(),
                $extraPayment->GetAmount()
            );

            $this->entityManager->persist($invoice);

            // associate the invoice with the extra payment
            $extraPayment->associateInvoice($invoice);

            $this->entityManager->persist($extraPayment);

            if ($doCommit) {
                $this->entityManager->flush();
                $this->entityManager->commit();
            }
        } catch (\Exception $e) {
            $this->entityManager->rollback();
        }
    }

    /**
     * Returns an array containing all types of ExtraPayments
     *
     * @return string[]
     */
    public function getAllTypes()
    {
        $query = new AllExtraPaymentTypes($this->entityManager);
        $result = [];
        foreach ($query() as $value) {
            array_push($result, $value['type']);
        }
        return $result;
    }

    /**
     * @param string $amount
     * @return string
     */
    private function formatAmount($amount)
    {
        return sprintf('%.2f €', intval($amount) / 100);
    }
    
    public function getFailedExtraData(array $filters = [], $count = false){
        $extra = $this->datatableService->getData('ExtraPayments', $filters, $count);

        if ($count) {
            return $extra;
        }
        

        return array_map(function (ExtraPayments $extra) {
            $customer = $extra->getCustomer();
            return [
                'e' => [
                    'id' => $extra->getId(),
                    'generatedTs' => $extra->getGeneratedTs()->format('Y-m-d H:i:s'),
                    'totalCost' =>  ($extra->getPayable()) ?  $extra->getAmount() : "FREE",
                    'reasons' => $extra->getReasons(),
                    'payed' => ($extra->getStatus() == 'payed_correctly' || $extra->getStatus() == 'invoiced') ? true : false,
                ],
                'cu' => [
                    'id' => $customer->getId(),
                    'name_surname' => $customer->getName() . ' ' . $customer->getSurname(),
                    'mobile' => $customer->getMobile(),
                ],
                'button' => $extra->getId()
            ];
        }, $extra);
    }
    
    public function getTotalExtra()
    {
        return $this->extraPaymentsRepository->countTotalExtra();
    }
    
    /**
     * @param integer $extraPaymentId
     * @return TripPayments
     */
    public function getExtraPaymentById($extraPaymentId){
        return $this->extraPaymentsRepository->findOneById($extraPaymentId);
    }
    
    public function setPayedCorrectly(ExtraPayments $extraPayment) {
        $extraPayment->setPayedCorrectly();
        $extraPayment->setInvoiceAble(true);
        $this->entityManager->persist($extraPayment);
        $this->entityManager->flush();

        return $extraPayment;
    }
    
    public function setPayedCorrectlyFirstTime(ExtraPayments $extraPayment) {
        $extraPayment->setPayedCorrectly();
        $extraPayment->setInvoiceAble(true);
        $extraPayment->setFirstExtraTryTs(new \DateTime());
        $this->entityManager->persist($extraPayment);
        $this->entityManager->flush();

        return $extraPayment;
    }
    
    public function setStatusWrongPayment(ExtraPayments $extraPayment) {
        $extraPayment->setWrongExtra();
        $this->entityManager->persist($extraPayment);
        $this->entityManager->flush();

        return $extraPayment;
    }
    
    public function setTrasaction(ExtraPayments $extraPayment, $transaction) {
        error_log($transaction->getId());
        $extraPayment->setTransaction($transaction);
        $this->entityManager->persist($extraPayment);
        $this->entityManager->flush();

        return $extraPayment;
    }
    
    public function getExtraPaymentsWrong(Customers $customer = null, $timestampEndParam = null)
    {
        return $this->extraPaymentsRepository->findExtraPaymentsWrong($customer, $timestampEndParam);
    }
    
    public function getExtraPaymentsWrongTime(Customers $customer = null, $start, $end, $condition = null, $limit = null)
    {
        return $this->extraPaymentsRepository->findWrongExtraPaymentsTime($customer, $start, $end, $condition, $limit);
    }

    /**
     * @param $start
     * @param $end
     * @param $condition
     * @param $limit
     * @return array
     */
    public function getWrongExtraPaymentsDetails($start, $end, $condition = null, $limit = null)
    {
        return $this->extraPaymentsRepository->getCountWrongExtraPayments($start, $end, $condition, $limit);
    }
    
    /**
     * @param null $timestampEndParam
     * @param null $condition
     * @param null $limit
     * @return array
     */

    public function getExtraPaymentsForPaymentDetails($timestampEndParam = null, $condition = null, $limit = null)
    {
        return $this->extraPaymentsRepository->getCountExtraPaymentsForPayment($timestampEndParam, $condition, $limit);
    }
    
    /**
     * @param Customers $customer optional parameter to filter the results by
     *  customer
     * @return PersistentCollection
     */
    public function getExtraPaymentsForPayment(Customers $customer = null, $timestampEndParam = null, $condition = null, $limit = null)
    {
        return $this->extraPaymentsRepository->findExtraPaymentsForPayment($customer, $timestampEndParam, $condition, $limit);
    }
    
    /**
     * @param Customers $customer
     * @return ExtraPayments[]
     */
    public function getFailedByCustomer(Customers $customer)
    {
        return $this->extraPaymentsRepository->findFailedByCustomer($customer);
    }
    
    public function setPayable(ExtraPayments $extraPayment, $payable) {
        $extraPayment->setPayable($payable);
        $this->entityManager->persist($extraPayment);
        $this->entityManager->flush();
    }
    
    public function setExtraFree(ExtraPayments $extraPayment, $payable, Webuser $webuser = null) {
        try {
            if ($extraPayment->isPaymentTried()) {
                if ($webuser instanceof Webuser) {
                    $this->cancelExtraPaymentTries($extraPayment, $webuser);
                    $extraPayment = $this->setPayable($extraPayment, $payable);
                }
            }

            //$this->entityManager->flush();
            //$this->entityManager->commit();
        } catch (\Exception $e) {
            $this->entityManager->rollback();
            throw $e;
        }
    }
    
    /**
     * This method is called if ExtraPaymentTries are present for the extra.
     *
     * First backup copies of the ExtraPayments are generated. Then those of the
     * ExtraPaymentTries are. Finally all ExtraPaymentTries are removed.
     *
     * @param ExtraPayments $extraPayment
     * @param Webuser $webuser
     */
    private function cancelExtraPaymentTries(ExtraPayments $extraPayment, Webuser $webuser) {
        $extraPaymentCanceled = new ExtraPaymentsCanceled(
                $extraPayment,
                $webuser
        );
        $this->entityManager->persist($extraPaymentCanceled); 

        foreach ($this->extraPaymentTriesService->getByExtraPayment($extraPayment) as $extraPaymentTry) {
            $extraPaymentTryCanceled = new ExtraPaymentTriesCanceled(
                    $extraPaymentTry,
                    $extraPaymentCanceled
            );
            $this->entityManager->persist($extraPaymentTryCanceled);
            $this->entityManager->remove($extraPaymentTry);
        }
        // Set customer's paymentAble to true to enable new cost computation
        // and to enable payment to be triggered by script
        $this->customersService->enableCustomerPayment($extraPayment->getCustomer());
        $this->entityManager->flush();





/*


        foreach ($this->tripPaymentsService->getByTrip($trip) as $tripPayment) {
            $tripPaymentCanceled = new TripPaymentsCanceled(
                $tripPayment,//<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
                $webuser
            );//<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
            $this->entityManager->persist($tripPaymentCanceled);//<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<

            foreach ($this->tripPaymentTriesService->getByTripPayment($tripPayment) as $tripPaymentTry) {
                $tripPaymentTryCanceled = new TripPaymentTriesCanceled(
                    $tripPaymentTry,//<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
                    $tripPaymentCanceled
                );//<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
                $this->entityManager->persist($tripPaymentTryCanceled);
                $this->entityManager->remove($tripPaymentTry);
            }//<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
        }
        // Set customer's paymentAble to true to enable new cost computation
        // and to enable payment to be triggered by script
        $this->customersService->enableCustomerPayment($trip->getCustomer());
        $this->entityManager->flush();
    }//<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
 * 
 */
    }
}
