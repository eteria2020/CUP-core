<?php

namespace SharengoCore\Service;

use Doctrine\ORM\EntityManager;
use Zend\Crypt\Password\Bcrypt;
use ZfcUser\Options\UserServiceOptionsInterface;
use ZfcUserDoctrineORM\Entity\User;


class UsersService implements ValidatorServiceInterface
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /** @var  User */
    private $userRepository;

    /**
     * @var UserServiceOptionsInterface
     */
    protected $options;

    /**
     * @param EntityManager               $entityManager
     * @param UserServiceOptionsInterface $options
     */
    public function __construct(EntityManager $entityManager, UserServiceOptionsInterface $options)
    {
        $this->entityManager = $entityManager;
        $this->userRepository = $this->entityManager->getRepository('\ZfcUserDoctrineORM\Entity\User');
        $this->options = $options;
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

    /**
     * @param User $user
     *
     * @return User
     */
    public function saveData(User $user)
    {
        $bcrypt = new Bcrypt();
        $bcrypt->setCost($this->options->getPasswordCost());
        $user->setPassword($bcrypt->create($user->getPassword()));

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }
}