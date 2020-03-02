<?php

namespace SharengoCore\Service;

// Internals
use SharengoCore\Entity\Repository\WebusersRepository;
// Externals
use Doctrine\ORM\EntityManager;

class WebusersService
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var WebusersRepository
     */
    private $webusersRepository;

    /**
     * @param WebusersRepository $webusersRepository
     * @param EntityManager $entityManager
     *
     */
    public function __construct(
        EntityManager $entityManager,
        $webusersRepository
    )
    {
        $this->entityManager = $entityManager;
        $this->webusersRepository = $webusersRepository;
    }

    /**
     * @param $id
     * @return \SharengoCore\Entity\Repository\Customers
     */
    public function findById($id)
    {
        return $this->webusersRepository->findById($id)[0];
    }

    /**
     * @param $email
     * @return |null
     */
    public function findByEmail($email)
    {
        $webuser = $this->webusersRepository->findByEmail($email);
        return (is_null($webuser) || !isset($webuser[0])) ? null : $webuser[0];
    }

}
