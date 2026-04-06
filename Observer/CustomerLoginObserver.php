<?php

declare(strict_types=1);

namespace MageMe\GA4\Observer;

use MageMe\GA4\Model\Config;
use MageMe\GA4\Model\EventCookie;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class CustomerLoginObserver implements ObserverInterface
{
    private Config $config;
    private EventCookie $eventCookie;

    public function __construct(Config $config, EventCookie $eventCookie)
    {
        $this->config = $config;
        $this->eventCookie = $eventCookie;
    }

    public function execute(Observer $observer): void
    {
        if (!$this->config->isEnabled() || !$this->config->isEventEnabled('login')) {
            return;
        }

        $this->eventCookie->addEvent('login', ['method' => 'email']);
    }
}
