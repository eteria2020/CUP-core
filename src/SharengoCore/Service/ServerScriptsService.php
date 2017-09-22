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
    
    public function writeRow(ServerScripts $serverScript) {
        $this->entityManager->persist($serverScript);
        $this->entityManager->flush();
    }
    
    public function getOldServerScript($dateStart) {
        return $this->serverScriptsRepository->getOldServerScript($dateStart);
    }

}
