<?php

namespace SharengoCore\Service;

use Doctrine\ORM\EntityManager;
use SharengoCore\Entity\UserEvents;

class UserEventsService {

    /**
     * @var EntityManager
     */
    private $entityManager;
    
    /**
     * @param EntityManager $entityManager
     */
    public function __construct(
        EntityManager $entityManager
    ){
        $this->entityManager = $entityManager;
    }
    
    public function saveUserEvents(UserEvents $userEvent) {
        $this->entityManager->persist($userEvent);
        $this->entityManager->flush();
        return $userEvent;
    }
}
