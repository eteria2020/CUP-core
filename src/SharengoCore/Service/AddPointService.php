<?php

namespace SharengoCore\Service;

use SharengoCore\Entity\Queries\AllAddPoint;

use Doctrine\ORM\EntityManager;

class AddPointService
{
    /**
     * @var EntityManager $entityManager
     */
    private $entityManager;

    /**
     * @param EntityManager $entityManager
     */
    public function __construct(
        EntityManager $entityManager
    ) {
        $this->entityManager = $entityManager;
    }

    /**
     * @return Array[]
     */
    public function getAllAddPoint()
    {
        $query = new AllAddPoint($this->entityManager);
        $ret = [];
        $ret['']='---';
        $addPoints = $query();
        
        foreach ($addPoints as $addPoint){
            $ret[$addPoint->getDescription()] = $addPoint->getDescription();

        }
        
        return $ret;
    }
}
