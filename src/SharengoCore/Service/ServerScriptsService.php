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
     * @var ServerScriptsService
     */
    private $serverScriptsService;
    
    /**
     * @var ServerScriptsRepository
     */
    private $serverScriptsRepository;

    /**
     * @param EntityManager $entityManager
     * @param ServerScriptsService $serverScriptsService
     */
    public function __construct(EntityManager $entityManager, ServerScriptsService $serverScriptsService)
    {
        $this->entityManager = $entityManager;
        $this->serverScriptsService = $serverScriptsService;
        $this->serverScriptsRepository = $this->entityManager->getRepository('\SharengoCore\Entity\ServerScripts');
    }
    
    public function test() {
        return $this->serverScriptsRepository->test();
    }

}
