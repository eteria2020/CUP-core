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
        $customers = $this->customersService->getListCustomers();
        $returnCustomers = [];
        $returnData = [];

        foreach ($customers as $value) {
            array_push($returnCustomers, $this->hydrator->extract($value));
        }
        
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
