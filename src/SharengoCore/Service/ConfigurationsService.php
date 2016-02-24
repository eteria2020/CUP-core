<?php

namespace SharengoCore\Service;

use Doctrine\ORM\EntityManager;
use SharengoCore\Entity\Configurations;
use SharengoCore\Entity\Repository\ConfigurationsRepository;
use SharengoCore\Exception\ConfigurationSaveAlarmException;
use Zend\Mvc\I18n\Translator;

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
     * @var Translator
     */
    private $translator;

    /**
     * ConfigurationsService constructor.
     *
     * @param EntityManager            $entityManager
     * @param ConfigurationsRepository $configurationsRepository
     */
    public function __construct(EntityManager $entityManager,
                                ConfigurationsRepository $configurationsRepository,
                                Translator $translator) {
        $this->entityManager = $entityManager;
        $this->configurationsRepository = $configurationsRepository;
        $this->translator = $translator;
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

    public function getConfigurationsKeyValueBySlug($slug)
    {
        $result = [];

        $configArray = $this->configurationsRepository->findBySlug($slug, true);
        foreach($configArray as $config) {
            $result[$config['configKey']] = $config['configValue'];
        }
        
        return $result;
    }

    /**
     * @param array $data
     */
    public function saveDataManageAlarm(array $data)
    {
        try {

            foreach($data['configurations'] as $value) {

                /** @var Configurations $configurations */
                $configurations = $this->configurationsRepository->find($value['id']);
                $configurations->setConfigValue($value['configValue']);
                $this->entityManager->persist($configurations);
            }

            $this->entityManager->flush();

        } catch(\Exception $e) {

            throw new ConfigurationSaveAlarmException($this->translator->translate("Si è verificato un errore durante il salvataggio della configurazione"));
        }
    }
}
