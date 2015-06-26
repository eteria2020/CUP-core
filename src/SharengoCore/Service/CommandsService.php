<?php

namespace SharengoCore\Service;

use Doctrine\ORM\EntityManager;
use SharengoCore\Entity\Commands;
use SharengoCore\Entity\Cars;
use SharengoCore\Entity\Repository\CommandsRepository;

class CommandsService
{
    /** @var EntityManager */
    private $entityManager;

    /** @var  CommandsRepository */
    private $commandsRepository;

    /**
     * @param EntityManager    $entityManager
     * @param CommandsRepository   $commandsRepository
     */
    public function __construct(
        EntityManager $entityManager,
        CommandsRepository $commandsRepository
    ) {
        $this->entityManager = $entityManager;
        $this->commandsRepository = $commandsRepository;
    }

    public function sendCommand(Cars $car, $commandIndex) {

        $command = Commands::createCommand($car, $commandIndex);

        $this->entityManager->persist($command);
        $this->entityManager->flush();

    }
    
}
