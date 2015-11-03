<?php

namespace SharengoCore\Service;

use SharengoCore\Entity\Customers;
use SharengoCore\Entity\Repository\CustomersNoteRepository;

class CustomersNoteService
{
    /**
     * @var CustomersNoteRepository
     */
    private $customersNoteRepository;

    /**
     * @param CustomersNoteRepository $customersNoteRepository
     */
    public function __construct(CustomersNoteRepository $customersNoteRepository)
    {
        $this->customersNoteRepository = $customersNoteRepository;
    }

    /**
     * @param Customers $customer
     * @return CustomersNote[]
     */
    public function getByCustomer(Customers $customer)
    {
        return $this->customersNoteRepository->findByCustomer($customer);
    }
}
