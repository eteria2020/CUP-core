<?php

namespace SharengoCore\Service;

use Doctrine\ORM\EntityManager;

use SharengoCore\Entity\Repository\WebuserRepository;
use SharengoCore\Entity\Repository\UserEventsRepository;

use SharengoCore\Entity\UserEvents;
use SharengoCore\Entity\Webuser;

class UserEventsService {

    /**
<<<<<<< HEAD
     *
     * @var EntityManager entityManager
     */
    private $entityManager;

    /*
     *  @var UserEventsRepository 
     */
    private $userEventsRepository;

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
        UserEventsRepository $userEventsRepository,
        WebuserRepository $webUserRepository
    ) {
        $this->entityManager = $entityManager;
        $this->userEventsRepository = $userEventsRepository;
        $this->webUserRepository = $webUserRepository;
    }

    public function saveUserEvents(UserEvents $userEvent) {
        $this->entityManager->persist($userEvent);
        $this->entityManager->flush();
        return $userEvent;
    }

    public function getListTripIdUserEventsBetweenDate($dateCurrentMonthStart, $dateNextMonthStart){
        $details = $this->userEventsRepository->getDetailsUserEventsBetweenDate($dateCurrentMonthStart, $dateNextMonthStart);
        $trips_id = array();
        foreach ($details as $detail) {
            $d = get_object_vars(json_decode($detail['details']));
            $a = get_object_vars($d['details']);
            $trips_id[] = (int)$a['trip_id'];
        }
        return $trips_id;
    }

    public function saveNewEvent(Webuser $webUser, $topic, array $details) {

        $userEvents = new UserEvents($webUser, $topic, $details);
        $this->entityManager->persist($userEvents);
        $this->entityManager->flush();

        return $userEvents;
    }

}
