<?php

namespace SharengoCore\Entity\Repository;

// Externals
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;
// Internals
use SharengoCore\Entity\Mails;

class MailsRepository extends EntityRepository
{
        public function findMails($type,$language)
    {
        $em = $this->getEntityManager();

        $dql = "SELECT m
        FROM \SharengoCore\Entity\Mails m
        WHERE type = :type AND language = :language";

        $query = $em->createQuery($dql);
        $query->setParameter('type', $type);
        $query->setParameter('language', $language);

        return $query->getResult();
    }
}
