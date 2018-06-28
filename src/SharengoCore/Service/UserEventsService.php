<?php

namespace SharengoCore\Service;

use Doctrine\ORM\EntityManager;
use SharengoCore\Entity\UserEvents;
use SharengoCore\Entity\Repository\UserEventsRepository;

class UserEventsService {

    /**
     * @var EntityManager
     */
    private $entityManager;
    
    /*
     *  @var UserEventsRepository 
     */
    private $userEventsRepository;
    
    /**
     * @param EntityManager $entityManager
     * @param EntityRepository $userEventsRepository
     */
    public function __construct(
        EntityManager $entityManager,
        UserEventsRepository $userEventsRepository
    ){
        $this->entityManager = $entityManager;
        $this->userEventsRepository = $userEventsRepository;
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
}
