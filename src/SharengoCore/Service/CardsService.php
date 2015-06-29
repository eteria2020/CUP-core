<?php

namespace SharengoCore\Service;

use SharengoCore\Entity\Cards;
use SharengoCore\Entity\Customers;
use SharengoCore\Entity\Repository\CardsRepository;

class CardsService
{
    /**
     * @var
     */
    private $entityManager;

    /** @var  CardsRepository */
    private $cardsRepository;

    public function __construct($entityManager)
    {
        $this->entityManager = $entityManager;
        $this->cardsRepository = $entityManager->getRepository('\SharengoCore\Entity\Cards');
    }

    /**
     * @param Customers $customer
     *
     * @return Cards
     */
    public function createVirtualCard(Customers $customer)
    {
        $code = 'RF' . str_pad($customer->getId(), 6, '0', STR_PAD_LEFT);
        $rfid = 'AP' . str_pad($customer->getId(), 6, '0', STR_PAD_LEFT);

        $card = new Cards();
        $card->setCode($code);
        $card->setRfid($rfid);
        $card->setIsAssigned(true);
        $card->setNotes('Temp Virtual Card');

        $this->entityManager->persist($card);
        $this->entityManager->flush();

        return $card;
    }

    public function ajaxCardCodeAutocomplete($query)
    {
        $cards = $this->cardsRepository->ajaxCardCodeAutocomplete($query);
        $as_cards = [];

        /** @var Cards $card */
        foreach ($cards as $card) {

            $as_cards[] = [
                'id'   => $card->getCode(),
                'name' => sprintf('Rfid: %s - Codice: %s', $card->getRfid(), $card->getCode())
            ];
        }

        return $as_cards;
    }

    public function getCard($code)
    {
        return $this->cardsRepository->findOneBy([
            'code' => $code
        ]);
    }
}
