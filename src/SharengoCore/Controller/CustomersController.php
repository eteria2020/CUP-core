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
            $filters['card_code'] = $this->params()->fromQuery('card_code');
        }

        // get customers
        $customers = $this->customersService->getListCustomersFilteredLimited($filters, $limit);

        // process customers
        foreach ($customers as $value) {
            array_push($returnCustomers, $this->hydrator->extract($value));
        }

        // return data
        $returnData['status'] = 200;
        $returnData['reason'] = '';
        $returnData['data'] = $returnCustomers;

       return new JsonModel($returnData);
    }

/*nella ricerca user c'è ma mettere la possibilità di filtrare anche per
- surname
- name
- phone (nel db c'è phone e mobile, filtra in or secondo me)
- card_code*/

}
