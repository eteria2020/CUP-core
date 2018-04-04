<?php

namespace SharengoCore\Service;

use Doctrine\ORM\EntityManager;
use SharengoCore\Entity\Commands;
use SharengoCore\Entity\Cars;
use SharengoCore\Entity\Webuser;
use SharengoCore\Entity\Repository\CommandsRepository;

class CommandsService {

    /** @var EntityManager */
    private $entityManager;

    /** @var  CommandsRepository */
    private $commandsRepository;

    /**
     * @param EntityManager    $entityManager
     * @param CommandsRepository   $commandsRepository
     */
    public function __construct(
    EntityManager $entityManager, CommandsRepository $commandsRepository
    ) {
        $this->entityManager = $entityManager;
        $this->commandsRepository = $commandsRepository;
    }

    /**
     * 
     * @param Cars $car
     * @param type $commandIndex
     * @param Webuser $webuser
     * @param type $intArg1
     * @param type $intArg2
     * @param type $txtArg1
     * @param type $txtArg2
     */
    public function sendCommand(Cars $car, $commandIndex, Webuser $webuser = null, $intArg1 = null, $intArg2 = null, $txtArg1 = null, $txtArg2 = null, $ttl = null) {
        $command = Commands::createCommand($car, $commandIndex, $webuser, $intArg1, $intArg2, $txtArg1, $txtArg2);

        if (!is_null($intArg1)) {
            $command->setIntarg1($intArg1);
        }

        if (!is_null($intArg2)) {
            $command->setIntarg2($intArg2);
        }

        if (!is_null($txtArg1)) {
            $command->setTxtarg1($txtArg1);
        }

        if (!is_null($txtArg2)) {
            $command->setTxtarg2($txtArg2);
        }

        if (!is_null($ttl)) {
            $command->setTtl($ttl);
        }

        $this->entityManager->persist($command);
        $this->entityManager->flush();
    }

}
