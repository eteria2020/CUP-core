<?php

namespace SharengoCore\Controller;

use Zend\Mvc\Controller\AbstractRestfulController;
use Zend\View\Model\JsonModel;
use Zend\Http\Client;
use SharengoCore\Entity\CustomersBonusPackages;
use SharengoCore\Service\CustomersBonusPackagesService;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;

class CustomersBonusPackagesController extends AbstractRestfulController
{

    /**
     * @var CustomersBonusPackagesService
     */
    private $customersBonusPackagesService;

    /**
     * @var DoctrineHydrator
     */
    private $hydrator;

    public function __construct(
        CustomersBonusPackagesService $customersBonusPackagesService,
        DoctrineHydrator $hydrator
    ) {
        $this->customersBonusPackagesService = $customersBonusPackagesService;
        $this->hydrator = $hydrator;
    }

    public function get($id)
    {
        $bonusPackage = $this->customersBonusPackagesService->getBonusPackageById($id);
        $bonusPackage = $bonusPackage->toArray($this->hydrator);

        return new JsonModel($this->buildReturnData(200, '', $bonusPackage));
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
