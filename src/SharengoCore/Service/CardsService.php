<?php

namespace SharengoCore\Service;

use SharengoCore\Entity\Cards;
use SharengoCore\Entity\Customers;

class CardsService
{
    /**
     * @var 
     */
    private $entityManager;

    public function __construct($entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param Customers $customer
     * @return Cards
     */
    public function createVirtualCard(Customers $customer)
    {
        $code = 'RF'.str_pad($customer->getId(), 6, '0', STR_PAD_LEFT);
        $rfid = 'AP'.str_pad($customer->getId(), 6, '0', STR_PAD_LEFT);

        $card = new Cards();
        $card->setCode($code);
        $card->setRfid($rfid);
        $card->setIsAssigned(true);
        $card->setNotes('Temp Virtual Card');

        $this->entityManager->persist($card);
        $this->entityManager->flush();

        return $card;
    }
}
