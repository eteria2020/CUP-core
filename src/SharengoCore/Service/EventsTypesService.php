<?php

namespace SharengoCore\Service;

use SharengoCore\Entity\Repository\EventsTypesRepository;

class EventsTypesService
{
    /**
     * @var EventsTypesRepository
     */
    private $eventsTypesRepository;

    private $cachedEventsTypes;

    public function __construct(EventsTypesRepository $eventsTypesRepository)
    {
        $this->eventsTypesRepository = $eventsTypesRepository;
    }

    /**
     * @return ArrayCollection
     */
    public function getAll()
    {
        return $this->eventsTypesRepository->findAll();
    }

    /**
     * @return ArrayCollection
     */
    public function getAllCached()
    {
        if (!$this->cachedEventsTypes) {
            $this->cachedEventsTypes = $this->getAll();
        }

        return $this->cachedEventsTypes;
    }

    /**
     * @return EventsTypes
     */
    public function mapEvent($event)
    {
        $eventsTypes = $this->getAllCached();

        foreach ($eventsTypes as $et) {
            if ($event->getLabel() === $et->getLabel()) {
                $mapLogic = $et->getMapLogic();
                if (empty($mapLogic)) {
                    return $et;
                }
                $reqs = explode('#', $mapLogic);
                $is_valid = true;
                foreach ($reqs as $req) {
                    list($key, $value) = explode('=', $req);
                    $methodName = 'get'.ucfirst($key);
                    if (!method_exists($event, $methodName) || $event->$methodName() != $value) {
                        $is_valid = false;
                    }
                }

                if ($is_valid) {
                    return $et;
                }
            }
        }

        return null;
    }
}
