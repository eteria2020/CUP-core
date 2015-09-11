<?php

namespace SharengoCore\Entity\Queries;

use Doctrine\ORM\EntityManagerInterface;

abstract class Query
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function __invoke()
    {
        $query = $this->em->createQuery($this->dql());

        foreach ($this->params() as $param => $value) {
            $query->setParameter($param, $value);
        }

        return $query->getResult();
    }

    /**
     * @return string expressing the dql
     */
    protected function dql()
    {
        return '';
    }

    /**
     * @return array keys are parameter names
     */
    protected function params()
    {
        return [];
    }
}
