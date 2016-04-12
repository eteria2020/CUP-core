<?php

namespace SharengoCore\Service;

use SharengoCore\Entity\Customers;
use SharengoCore\Entity\ProviderAuthenticatedCustomer;
use SharengoCore\Entity\Repository\ProviderAuthenticatedCustomersRepository;
use SharengoCore\Exception\ProviderAuthenticatedCustomerNotFoundException;

use Doctrine\ORM\EntityManager;

class ProviderAuthenticatedCustomersService
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var ProviderAuthenticatedCustomersRepository
     */
    private $providerAuthenticatedCustomerRepository;

    public function __construct(
        EntityManager $entityManager,
        ProviderAuthenticatedCustomersRepository $repository
    ) {
        $this->entityManager = $entityManager;
        $this->providerAuthenticatedCustomerRepository = $repository;
    }

    /**
     * @param string uuid
     * @return ProviderAuthenticatedCustomer
     * @throws ProviderAuthenticatedCustomerNotFoundException
     */
    public function getCustomerById($id)
    {
        $customer = $this->providerAuthenticatedCustomerRepository->findById($id);

        if (!$customer instanceof ProviderAuthenticatedCustomer) {
            throw new ProviderAuthenticatedCustomerNotFoundException();
        }

        return $customer;
    }

    public function linkCustomer(
        ProviderAuthenticatedCustomer $providerAuthenticatedCustomer,
        Customers $customer
    ) {
        $providerAuthenticatedCustomer->linkCustomer($customer);

        $this->entityManager->persist($providerAuthenticatedCustomer);
        $this->entityManager->flush();
    }
}
