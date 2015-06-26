<?php

namespace SharengoCore\Controller;

use Zend\Mvc\Controller\AbstractRestfulController;
use Zend\View\Model\JsonModel;
use Zend\Http\Client;
use SharengoCore\Service\CustomersService;

class CustomersController extends AbstractRestfulController
{

    /**
     * @var CustomersService
     */
    private $customersService;

    public function __construct(
        CustomersService $customersService
    ) {
        $this->customersService = $customersService;
    }

    public function getList()
    {
        $returnCustomers = [];
        $returnData = [];

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
        foreach ($customers as $value) {
            $customer = $this->customersService->toArray($value);
            array_push($returnCustomers, $customer);
        }

        // return data
        $returnData['status'] = 200;
        $returnData['reason'] = '';
        $returnData['data'] = $returnCustomers;

        return new JsonModel($returnData);
    }

}
