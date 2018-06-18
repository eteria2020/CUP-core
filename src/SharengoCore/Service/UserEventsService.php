<?php

namespace SharengoCore\Service;

use Doctrine\ORM\EntityManager;
use SharengoCore\Entity\Repository\WebuserRepository;
//use SharengoCore\Entity\Repository\UserEventsRepository;
use SharengoCore\Entity\Webuser;
use SharengoCore\Entity\UserEvents;

class UserEventsService {

    /**
     *
     * @var EntityManager entityManager
     */
    private $entityManager;

    /**
     *
     * @var WebUserRepository webUserRepository
     */
    private $webUserRepository;

    /**
     * 
     * @param EntityManager $entityManager
     * @param WebuserRepository $webUserRepository
     */
    public function __construct(
        EntityManager $entityManager,
        WebuserRepository $webUserRepository
    ) {
        $this->entityManager = $entityManager;
        $this->webUserRepository = $webUserRepository;
    }

    public function saveNewEvent(Webuser $webUser, $topic, array $details) {

        $userEvents = new UserEvents($webUser, $topic, $details);
        $this->entityManager->persist($userEvents);
        $this->entityManager->flush();

        return $userEvents;
    }
}

