<?php

declare(strict_types=1);

namespace MageMe\GA4\Observer;

use MageMe\GA4\Model\Config;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\View\LayoutInterface;

class SetOrderDataObserver implements ObserverInterface
{
    private Config $config;
    private LayoutInterface $layout;

    public function __construct(Config $config, LayoutInterface $layout)
    {
        $this->config = $config;
        $this->layout = $layout;
    }

    public function execute(EventObserver $observer): void
    {
        if (!$this->config->isEnabled() || !$this->config->isEventEnabled('purchase')) {
            return;
        }

        $orderIds = $observer->getEvent()->getOrderIds();
        if (empty($orderIds) || !is_array($orderIds)) {
            return;
        }

        $block = $this->layout->getBlock('mageme_ga4_purchase');
        if ($block) {
            $block->setOrderIds($orderIds);
        }
    }
}
