<?php

namespace SharengoCore\Service;

// Internals
use SharengoCore\Entity\Webuser;
use SharengoCore\Entity\Repository\WebuserRepository;
// Externals
use Doctrine\ORM\EntityManager;
use Zend\Crypt\Password\Bcrypt;
use ZfcUser\Options\UserServiceOptionsInterface;

class UsersService implements ValidatorServiceInterface
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var UserServiceOptionsInterface
     */
    protected $options;

    /**
     * @var WebuserRepository
     */
    private $userRepository;

    /** 
     * @var DatatableServiceInterface
     */
    private $datatableService;

    /**
     * @param EntityManager $entityManager
     * @param UserServiceOptionsInterface $options
     */
    public function __construct(
        EntityManager $entityManager,
        UserServiceOptionsInterface $options,
        WebuserRepository $userRepository,
        DatatableServiceInterface $datatableService
    ) {
        $this->entityManager = $entityManager;
        $this->options = $options;
        $this->userRepository = $userRepository;
        $this->datatableService = $datatableService;
    }

    /**
     * @return mixed
     */
    public function getListUsers()
    {
        return $this->userRepository->findAll();
    }

    public function getTotalUsers()
    {
        return $this->userRepository->getTotalUsers();
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

    public function getDataDataTable(array $filters = [], $count = false)
    {
        $webusers = $this->datatableService->getData('Webuser', $filters, $count);

        if ($count) {
            return $webusers;
        }

        return array_map(function (Webuser $webuser) {
            return [
                'e' => [
                    'id' => $webuser->getId(),
                    'displayName' => $webuser->getDisplayName(),
                    'email' => $webuser->getEmail(),
                    'role' => $webuser->getRole(),
                ],
                'button' => $webuser->getId()
            ];
        }, $webusers);
    }
}