<?php

namespace SharengoCore\Entity\Queries;

use Doctrine\ORM\EntityManagerInterface;

abstract class Query
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var string
     */
    private $resultMethod;

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

        $resultCallable = [$query, $this->resultMethod()];

        return $resultCallable();
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

    protected function resultMethod()
    {
        return 'getResult';
    }
}
