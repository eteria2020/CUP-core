<?php

namespace SharengoCore\Entity\Repository;

// Externals
use Doctrine\ORM\Query\ResultSetMapping;
// Internals
use SharengoCore\Entity\Mails;

class MailsRepository extends \Doctrine\ORM\EntityRepository
{
    public function findMails($category, $language)
    {
        $em = $this->getEntityManager();

        $dql = "SELECT m
        FROM \SharengoCore\Entity\Mails m
        WHERE m.category = :category AND m.language = :language
        AND m.enable = TRUE";

        $query = $em->createQuery($dql);
        $query->setParameter('category', $category);
        $query->setParameter('language', $language);
        $query->setMaxResults(1);
        return $query->getResult();
    }
}
