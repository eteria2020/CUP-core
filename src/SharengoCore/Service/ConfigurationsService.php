<?php

namespace SharengoCore\Service;

use Doctrine\ORM\EntityManager;
use SharengoCore\Entity\Configurations;
use SharengoCore\Entity\Repository\ConfigurationsRepository;

/**
 * Class ConfigurationsService
 * @package SharengoCore\Service
 */
class ConfigurationsService
{
    /** @var EntityManager */
    private $entityManager;

    /**
     * @var ConfigurationsRepository
     */
    private $configurationsRepository;

    /**
     * ConfigurationsService constructor.
     *
     * @param EntityManager            $entityManager
     * @param ConfigurationsRepository $configurationsRepository
     */
    public function __construct(EntityManager $entityManager, ConfigurationsRepository $configurationsRepository) {
        $this->entityManager = $entityManager;
        $this->configurationsRepository = $configurationsRepository;
    }

    /**
     * @param      $slug
     * @param null $arrayResult
     *
     * @return array
     */
    public function getConfigurationsBySlug($slug, $arrayResult = null)
    {
        return $this->configurationsRepository->findBySlug($slug, $arrayResult);
    }

    /**
     * @param array $data
     */
    public function saveDataManageAlarm(array $data)
    {
        foreach($data['configurations'] as $value) {

            /** @var Configurations $configurations */
            $configurations = $this->configurationsRepository->find($value['id']);
            $configurations->setConfigValue($value['configValue']);
            $this->entityManager->persist($configurations);
        }

        $this->entityManager->flush();
    }
}
