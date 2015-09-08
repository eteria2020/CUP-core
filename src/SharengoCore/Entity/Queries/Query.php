<?php

namespace SharengoCore\Entity\Queries;

use Doctrine\ORM\EntityManagerInterface;

abstract class Query
{
    private $em;

    protected $dql;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function __invoke()
    {
        $query = $this->em->createQuery($this->dql);

        return $query->getResult();
    }
}
