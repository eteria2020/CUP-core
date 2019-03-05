<?php

namespace SharengoCore\Controller;

use SharengoCore\Service\MunicipalitiesService;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;

class MunicipalitiesController extends AbstractActionController
{
    /**
     * @var MunicipalitiesService $municipalitiesService
     */
    private $municipalitiesService;

    public function __construct($municipalitiesService)
    {
        $this->municipalitiesService = $municipalitiesService;
    }

    public function activeMunicipalitiesAction()
    {
        $province = $this->params('province');

        $municipalities = $this->municipalitiesService->activeMunicipalities($province);

        $municipalities = array_map(function ($municipality) {
            return [
                'id' => $municipality->getId(),
                'name' => $municipality->getName(),
                'province' => $municipality->getProvince(),
                'zip_codes' => json_decode($municipality->getZipCodes())
            ];
        }, $municipalities);

        return new JsonModel($municipalities);
    }
}
