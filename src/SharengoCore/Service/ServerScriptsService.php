<?php

namespace SharengoCore\Service;

use SharengoCore\Entity\ServerScripts;
use SharengoCore\Entity\Repository\ServerScriptsRepository;

use Doctrine\ORM\EntityManager;

class ServerScriptsService
{
    /**
     * @var EntityManager
     */
    private $entityManager;
    
    /**
     * @var ServerScriptsRepository
     */
    private $serverScriptsRepository;

    /**
     * @param EntityManager $entityManager
     * @param ServerScriptsService $serverScriptsService
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->serverScriptsRepository = $this->entityManager->getRepository('\SharengoCore\Entity\ServerScripts');
    }
    
    public function writeEndServerScript(ServerScripts $serverScript) {
        $serverScript->setEndTs(new \DateTime());
        $this->writeRow($serverScript);
    }
    
    public function writeStartServerScript(ServerScripts $serverScript){
        $serverScript->setStartTs(new \DateTime());
        $this->writeRow($serverScript);
    }
    
    public function writeRow(ServerScripts $serverScript) {
        $this->entityManager->persist($serverScript);
        $this->entityManager->flush();
    }
    
    public function getOldServerScript($dateStart) {
        $dateEnd = new \DateTime($dateStart);
        $dateEnd = $dateEnd->modify("+1 day");
        $dateEnd = $dateEnd->format("Y-m-d");
        return $this->serverScriptsRepository->getOldServerScript($dateStart, $dateEnd);
    }

}
