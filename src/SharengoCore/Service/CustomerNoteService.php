<?php

namespace SharengoCore\Service;

use SharengoCore\Entity\Customers;
use SharengoCore\Entity\CustomerNote;
use SharengoCore\Entity\Webuser;
use SharengoCore\Entity\Repository\CustomerNoteRepository;
use SharengoCore\Exception\NoteContentNotValidException;

use Doctrine\ORM\EntityManager;

class CustomerNoteService
{
    /**
     * @var CustomerNoteRepository
     */
    private $customerNoteRepository;

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @param CustomerNoteRepository $customerNoteRepository
     * @param EntityManager $entityManager
     */
    public function __construct(
        CustomerNoteRepository $customerNoteRepository,
        EntityManager $entityManager
    ) {
        $this->customerNoteRepository = $customerNoteRepository;
        $this->entityManager = $entityManager;
    }

    /**
     * @param Customers $customer
     * @return CustomerNote[]
     */
    public function getByCustomer(Customers $customer)
    {
        return $this->customerNoteRepository->findByCustomer($customer);
    }

    /**
     * @param Customers $customer
     * @param Webusers $webuser
     * @param string $content
     */
    public function addNote(Customers $customer, Webuser $webuser, $content)
    {
        $note = new CustomerNote($customer, $webuser, $content);
        $this->entityManager->persist($note);
        $this->entityManager->flush();
    }

    /**
     * @param string $content
     * @throws NoteContentNotValidException
     */
    public function verifyContent($content)
    {
        if ($content == "") {
            throw new NoteContentNotValidException();
        }
    }
}
