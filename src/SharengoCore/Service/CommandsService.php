<?php

namespace SharengoCore\Service;

use Doctrine\ORM\EntityManager;
use SharengoCore\Entity\Commands;
use SharengoCore\Entity\Repository\CommandsRepository;

class CommandsService
{
    /**
     * @var CommandsRepository
     */
    private $repository;

    /**
     * @var EntityManager
     */
    private $entityManager;

    public function __construct(
        EntityManager $entityManager,
        CommandsRepository $repository
    ) {
        $this->entityManager = $entityManager;
        $this->repository = $repository;
    }

    public function createCommand($plate, $toSend, $action, $txtarg1 = null)
    {
        $command = new Commands();

        $command->setCarPlate($plate);
        $command->setToSend($toSend);
        $command->setCommand($action);
        $command->setTxtarg1($txtarg1);
        $command->setQueued(new \DateTime());

        $this->entityManager->persist($command);
        $this->entityManager->flush();

        return $command;
    }
}
