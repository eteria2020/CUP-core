<?php

namespace SharengoCore\Controller;

use Zend\Mvc\Controller\AbstractRestfulController;
use Zend\View\Model\JsonModel;
use Zend\Http\Client;
use SharengoCore\Service\CustomersService;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;

class CustomersController extends AbstractRestfulController
{

    /**
     * @var CustomersService
     */
    private $customersService;

    /**
     * @var DoctrineHydrator
     */
    private $hydrator;

    public function __construct(
        CustomersService $customersService,
        DoctrineHydrator $hydrator
    ) {
        $this->customersService = $customersService;
        $this->hydrator = $hydrator;
    }

    public function getList()
    {
        $returnCustomers = [];

        // get limit
        $limit = $this->params()->fromQuery('limit');
        if ($limit === null || $limit > 10 || $limit <= 0) {
            $limit = 10;
        }

        // get filters
        $filters = [];
        if ($this->params()->fromQuery('surname') !== null) {
            $filters['surname'] = $this->params()->fromQuery('surname');
        }
        if ($this->params()->fromQuery('name') !== null) {
            $filters['name'] = $this->params()->fromQuery('name');
        }
        if ($this->params()->fromQuery('phone') !== null) {
            $filters['mobile'] = $this->params()->fromQuery('phone');
        }
        if ($this->params()->fromQuery('card_code') !== null) {
            $filters['card'] = $this->params()->fromQuery('card_code');
        }

        // get customers
        $customers = $this->customersService->getListCustomersFilteredLimited($filters, $limit);

        // process customers
        foreach ($customers as $customer) {
            $customer = $customer->toArray($this->hydrator);
            array_push($returnCustomers, $customer);
        }

        return new JsonModel($this->buildReturnData(200, '', $returnCustomers));
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
