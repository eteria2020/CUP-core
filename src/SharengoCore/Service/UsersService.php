<?php

namespace SharengoCore\Service;

use Doctrine\ORM\EntityManager;
use Zend\Crypt\Password\Bcrypt;
use ZfcUser\Options\UserServiceOptionsInterface;
use Application\Entity\Webuser;


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
        $this->userRepository = $this->entityManager->getRepository('\Application\Entity\Webuser');
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
    public function saveData(Webuser $user)
    {
        $bcrypt = new Bcrypt();
        $bcrypt->setCost($this->options->getPasswordCost());
        $user->setPassword($bcrypt->create($user->getPassword()));

        // only role admin is allowed
        $user->setRole('admin');

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }
}