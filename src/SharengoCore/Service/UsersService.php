<?php

namespace SharengoCore\Service;

use ZfcUserDoctrineORM\Entity\User;


class UsersService
{
    private $entityManager;

    /** @var  User */
    private $userRepository;

    /**
     * @param $entityManager
     */
    public function __construct($entityManager)
    {
        $this->entityManager = $entityManager;

        $this->userRepository = $this->entityManager->getRepository('\ZfcUserDoctrineORM\Entity\User');
    }

    /**
     * @return mixed
     */
    public function getListUsers()
    {
        return $this->userRepository->findAll();
    }

    public function findByEmail($email)
    {
        return $this->userRepository->findBy(array('email' => $email));
    }
}