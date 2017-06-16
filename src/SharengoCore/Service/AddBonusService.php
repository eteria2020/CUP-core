<?php

namespace SharengoCore\Service;

use SharengoCore\Entity\Queries\AllAddBonus;

use Doctrine\ORM\EntityManager;

class AddBonusService
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
    public function getAllAddBonus()
    {
        $query = new AllAddBonus($this->entityManager);
        $ret = [];
        $addBonuses = $query();
        
        foreach ($addBonuses as $addBonus){
            $ret[$addBonus->getDescription()] = $addBonus->getDescription();

        }
        
        return $ret;
    }
}
