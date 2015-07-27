<?php

namespace SharengoCore\Controller;

use Zend\Mvc\Controller\AbstractRestfulController;
use Zend\View\Model\JsonModel;
use Zend\Http\Client;
use SharengoCore\Service\InvoicesService;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;
use Zend\Authentication\AuthenticationService;
use SharengoCore\Entity\Customers;

class InvoicesController extends AbstractRestfulController
{

    /**
     * @var InvoicesService
     */
    private $invoicesService;

    /**
     * @var DoctrineHydrator
     */
    private $hydrator;

    /**
     * @var AuthenticationService
     */
    private $authService;

    public function __construct(
        InvoicesService $invoicesService,
        DoctrineHydrator $hydrator,
        AuthenticationService $authService
    ) {
        $this->invoicesService = $invoicesService;
        $this->hydrator = $hydrator;
        $this->authService = $authService;
    }

    public function getList()
    {
        $status = 200;
        $reason = '';

        $extractedInvoices = [];

        // get user id from AuthService
        $user = $this->authService->getIdentity();

        if($user != null && $user instanceof Customers) {

            $date = null;

            if ($this->params()->fromQuery('date') != null) {
                $date = $this->params()->fromQuery('date');
            } elseif ($this->params()->fromQuery('shortDate')) {
                $date = $this->params()->fromQuery('shortDate');
            }

            // get invoices
            $invoices = $this->invoicesService->getInvoicesByCustomerWithDate($user, $date);
            foreach ($invoices as $invoice) {
                array_push($extractedInvoices, $invoice->toArray($this->hydrator));
            }
            $reason = 'OK';

        } else {
            $status = 401;
            $reason = 'Authentication failed';
        }

        return new JsonModel($this->buildReturnData(200, $reason, $extractedInvoices));
    }

    /**
     * @param  integer
     * @param  string
     * @param  mixed[]
     * @return mixed[]
     */
    private function buildReturnData($status, $reason, $data = [])
    {
        $returnData = [];
        $returnData['status'] = $status;
        $returnData['reason'] = $reason;
        $returnData['data'] = $data;
        return $returnData;
    }
}
