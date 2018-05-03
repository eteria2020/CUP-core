<?php

namespace SharengoCore\Service;

use SharengoCore\Entity\Repository\ExtraPaymentsRepository;
use SharengoCore\Entity\ExtraPayments;
use SharengoCore\Entity\Customers;
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
     * @var ExtraPayments
     */
    private $extraPaymentsRepository;

    /**
     * @param EntityManager $entityManager
     * @param InvoicesService $invoicesService
     * @param DatatableServiceInterface $datatableService
     * @param ExtraPaymentsRepository $extraPaymentsRepository
     */
    public function __construct(
        EntityManager $entityManager,
        InvoicesService $invoicesService,
        DatatableServiceInterface $datatableService,
        ExtraPaymentsRepository $extraPaymentsRepository
    ) {
        $this->entityManager = $entityManager;
        $this->invoicesService = $invoicesService;
        $this->datatableService = $datatableService;
        $this->extraPaymentsRepository = $extraPaymentsRepository;
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
    
    public function getFailedExtraData(array $filters = [], $count = false) {
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
                    'totalCost' => $extra->getAmount(),
                    'reasons' => $extra->getReasons(),
                    'payed' => ($extra->getStatus() == 'payed_correctly') ? true : false,
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

}
