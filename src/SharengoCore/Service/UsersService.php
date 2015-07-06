<?php

namespace SharengoCore\Service;

use Doctrine\ORM\EntityManager;
use Zend\Crypt\Password\Bcrypt;
use ZfcUser\Options\UserServiceOptionsInterface;
use SharengoCore\Entity\Webuser;


class UsersService implements ValidatorServiceInterface
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /** @var  Webuser */
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
        $this->userRepository = $this->entityManager->getRepository('\SharengoCore\Entity\Webuser');
        $this->options = $options;
    }

    /**
     * @return mixed
     */
    public function getListUsers()
    {
        return $this->userRepository->findAll();
    }

    public function findUserById($userId)
    {
        return $this->userRepository->find($userId);
    }

    public function findByEmail($email)
    {
        return $this->userRepository->findBy(['email' => $email]);
    }

    /**
     * @param Webuser $user
     *
     * @return Webuser
     */
    public function saveData(Webuser $user, $pwd = null)
    {
        $password = $user->getPassword();

        if(empty($password)) {

            $user->setPassword($pwd);

        } else {

            $bcrypt = new Bcrypt();
            $bcrypt->setCost($this->options->getPasswordCost());
            $user->setPassword($bcrypt->create($user->getPassword()));
        }

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }
}