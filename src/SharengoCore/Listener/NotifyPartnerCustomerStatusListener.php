<?php

namespace SharengoCore\Listener;

use SharengoCore\Service\PartnerService;

use Zend\EventManager\SharedListenerAggregateInterface;
use Zend\EventManager\SharedEventManagerInterface;
use Zend\EventManager\EventInterface;

final class NotifyPartnerCustomerStatusListener implements SharedListenerAggregateInterface
{
    /**
     *
     * @var PartnerService
     */
    private $partnerService;

    public function __construct($partnerService)
    {
        $this->partnerService = $partnerService;
    }

        public function attachShared(SharedEventManagerInterface $events)
    {
        $this->listeners[] = $events->attach(
            'PartnerService',
            'notifyPartnerCustomerStatus',
            [$this, 'notifyCustomerStatusIsChanged']
        );

    }

    public function detachShared(SharedEventManagerInterface $events)
    {
        foreach ($this->listeners as $index => $callback) {
            if ($events->detach($callback)) {
                unset($this->listeners[$index]);
            }
        }
    }

    public function notifyCustomerStatusIsChanged(EventInterface $e)
    {
        $params = $e->getParams();
        $customer = $params['customer'];

        $this->partnerService->notifyCustomerStatus($customer);
    }
}