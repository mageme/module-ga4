<?php

declare(strict_types=1);

namespace MageMe\GA4\Observer;

use MageMe\GA4\Model\Config;
use MageMe\GA4\Model\EventCookie;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Store\Model\StoreManagerInterface;

class CompareAddObserver implements ObserverInterface
{
    private Config $config;
    private EventCookie $eventCookie;
    private StoreManagerInterface $storeManager;

    public function __construct(
        Config $config,
        EventCookie $eventCookie,
        StoreManagerInterface $storeManager
    ) {
        $this->config = $config;
        $this->eventCookie = $eventCookie;
        $this->storeManager = $storeManager;
    }

    public function execute(Observer $observer): void
    {
        if (!$this->config->isEnabled() || !$this->config->isEventEnabled('add_to_compare')) {
            return;
        }

        $product = $observer->getEvent()->getData('product');
        if (!$product) {
            return;
        }

        $price = (float) number_format((float) $product->getFinalPrice(), 2, '.', '');
        $currency = $this->storeManager->getStore()->getCurrentCurrencyCode();

        $this->eventCookie->addEvent('add_to_compare', [
            'currency' => $currency,
            'value' => $price,
            'items' => [[
                'item_id' => $product->getSku(),
                'item_name' => $product->getName(),
                'price' => $price,
            ]],
        ]);
    }
}
