<?php

namespace SharengoCore\Entity\Repository;


use Doctrine\ORM\Query\ResultSetMapping;

class UserEventsRepository extends \Doctrine\ORM\EntityRepository
{
    public function getDetailsUserEventsBetweenDate($dateCurrentMonthStart, $dateNextMonthStart) {
        $em = $this->getEntityManager();

        $dql = "SELECT u.details "
                . "FROM \SharengoCore\Entity\UserEvents u "
                . "WHERE u.insertTs >= :dateCurrentMonthStart "
                . "AND u.insertTs < :dateNextMonthStart "
                . "AND u.topic = :topic ";

        $query = $em->createQuery($dql);
        $query->setParameter('dateCurrentMonthStart', $dateCurrentMonthStart);
        $query->setParameter('dateNextMonthStart', $dateNextMonthStart);
        $query->setParameter('topic', "trips");

        return $query->getResult();
    }
}
