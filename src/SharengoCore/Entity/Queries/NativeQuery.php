<?php

namespace SharengoCore\Entity\Queries;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\ResultSetMapping;

abstract class NativeQuery
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var string
     */
    private $resultMethod;

    /**
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function __invoke()
    {
        $rsm = new ResultSetMapping;

        foreach ($this->scalarResults() as $result) {
            $rsm->addScalarResult(
                $result['column_name'],
                $result['alias'],
                $result['type']
            );
        }

        $query = $this->em->createNativeQuery($this->sql(), $rsm);

        foreach ($this->params() as $param => $value) {
            $query->setParameter($param, $value);
        }

        $resultCallable = [$query, $this->resultMethod()];

        return $resultCallable();
    }

    /**
     * @return string expressing the dql
     */
    protected function sql()
    {
        return '';
    }

    /**
     * Each row of the returned array contains three key => value pairs.
     * The keys are column_name, alias and type and they refer to the parameters
     * accepted by the method ResultSetMapping->addScalarResult()
     * @return array
     */
    protected function scalarResults()
    {
        return [];
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
