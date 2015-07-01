<?php

namespace SharengoCore\Controller;

use Zend\Mvc\Controller\AbstractRestfulController;
use Zend\View\Model\JsonModel;
use Zend\Http\Client;
use SharengoCore\Service\ReservationsService;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject as DoctrineHydrator;

class ReservationsController extends AbstractRestfulController
{

    /**
     * @var ReservationsService
     */
    private $reservationsService;

    /**
     * @var DoctrineHydrator
     */
    private $hydrator;

    public function __construct(
        ReservationsService $reservationsService,
        DoctrineHydrator $hydrator
    ) {
        $this->reservationsService = $reservationsService;
        $this->hydrator = $hydrator;
    }

    public function getList()
    {
        $returnReservations = [];

        // get filters
        $filters = [];
        if ($this->params()->fromQuery('plate') !== null) {
            $filters['car'] = $this->params()->fromQuery('plate');
        }
        if ($this->params()->fromQuery('active') !== null) {
            $filters['active'] = $this->params()->fromQuery('active');
        }

        // get reservations
        $reservations = $this->reservationsService->getListReservationsFiltered($filters);

        // process reservations
        foreach ($reservations as $reservation) {
            $reservation = $reservation->toArray($this->hydrator);
            array_push($returnReservations, $reservation);
        }

        return new JsonModel($this->buildReturnData(200, '', $returnReservations));
    }
 
    public function get($id)
    {
        
    }
 
    public function create($data)
    {
        
    }
 
    public function update($id, $data)
    {
        
    }
 
    public function delete($id)
    {
        
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
