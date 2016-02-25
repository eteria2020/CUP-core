<?php

namespace SharengoCore\Service;

use SharengoCore\Entity\Cards;
use SharengoCore\Entity\Customers;
use SharengoCore\Entity\Repository\CardsRepository;
use SharengoCore\Service\DatatableQueryBuilders\Basic;
use Zend\Mvc\I18n\Translator;
use Zend\Validator\IsInstanceOf;

class CardsService
{
    /**
     * @var
     */
    private $entityManager;

    /**
     * @var DatatableService
     */
    private $datatableService;

    /** @var  CardsRepository */
    private $cardsRepository;

    /** @var  Translator */
    private $translator;

    public function __construct($entityManager, DatatableService $datatableService, Translator $translator)
    {
        $this->entityManager = $entityManager;
        $this->cardsRepository = $entityManager->getRepository('\SharengoCore\Entity\Cards');
        $this->datatableService = $datatableService;
        $this->translator = $translator;
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
                'name' => sprintf($this->translator->translate('Rfid: %s - Codice: %s'), $card->getRfid(), $card->getCode())
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

    public function getDataDataTable(array $as_filters = [], $count = false)
    {
        $cards = $this->datatableService->getData('Cards', $as_filters, $count);

        if ($count) {
            return $cards;
        }

        return array_map(function (Cards $card) {
            return [
                'e' => [
                    'rfid'       => $card->getRfid(),
                    'code'       => $card->getCode(),
                    'isAssigned' => $card->getIsAssigned() ? $this->translator->translate('Si') : $this->translator->translate('No'),
                    'notes'      => $card->getNotes(),
                    'assignable' => $card->getAssignable() ? $this->translator->translate('Si') : $this->translator->translate('No'),
                ],
                'cu' => [
                    'surname'   => is_object($card->getCustomer()) ? $card->getCustomer()->getSurname() .' '. $card->getCustomer()->getName() : ''
                ]
            ];
        }, $cards);
    }

    public function createCard(Cards $card, $customer = null)
    {
        $rfid = $this->cardsRepository->getLastCardRfid();
        $newRfid = sprintf('CARD%d', ($rfid['lastrfid'] + 1));
        $card->setRfid($newRfid);
        $card->setIsAssigned(false);
        $card->setAssignable(true);

        if($customer instanceof Customers) {
            $customer->setCard($card);
            $card->setIsAssigned(true);
            $card->setAssignable(false);
            $this->entityManager->persist($customer);
        }

        $this->entityManager->persist($card);
        $this->entityManager->flush();

        return $card;
    }

    public function getTotalCards()
    {
        return $this->cardsRepository->getTotalCards();
    }
}
