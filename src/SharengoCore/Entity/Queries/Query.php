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

    public function __construct(EntityManagerInterface $em, $resultMethod = null)
    {
        $this->em = $em;

        // by default we use getResult as resultMethod
        if (is_null($resultMethod)) {
            $this->resultMethod = 'getResult';
        } else {
            $this->resultMethod = $resultMethod;
        }
    }

    public function __invoke()
    {
        $query = $this->em->createQuery($this->dql());

        foreach ($this->params() as $param => $value) {
            $query->setParameter($param, $value);
        }

        $resultCallable = [$query, $this->resultMethod];

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
}
