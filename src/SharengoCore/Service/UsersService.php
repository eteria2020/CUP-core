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

    /** @var bool */
    private $editMode = false;

    /** @var */
    private $validatorEmail;

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

        // only role admin is allowed
        $user->setRole('admin');

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }

    /**
     * @return boolean
     */
    public function getEditMode()
    {
        return $this->editMode;
    }

    /**
     * @param boolean $editMode
     */
    public function setEditMode($editMode)
    {
        $this->editMode = $editMode;
    }

    /**
     * @return mixed
     */
    public function getValidatorEmail()
    {
        return $this->validatorEmail;
    }

    /**
     * @param mixed $validatorEmail
     */
    public function setValidatorEmail($validatorEmail)
    {
        $this->validatorEmail = $validatorEmail;
    }
}