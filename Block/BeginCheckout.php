<?php

declare(strict_types=1);

namespace MageMe\GA4\Block;

use MageMe\GA4\Model\Config;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

class BeginCheckout extends Template
{
    private Config $config;
    private CheckoutSession $checkoutSession;
    private SerializerInterface $serializer;

    public function __construct(
        Context $context,
        Config $config,
        CheckoutSession $checkoutSession,
        SerializerInterface $serializer,
        array $data = []
    ) {
        $this->config = $config;
        $this->checkoutSession = $checkoutSession;
        $this->serializer = $serializer;
        parent::__construct($context, $data);
    }

    public function getTrackingData(): string
    {
        $quote = $this->checkoutSession->getQuote();
        if (!$quote || !$quote->getItemsCount()) {
            return '';
        }

        $items = [];
        foreach ($quote->getAllVisibleItems() as $item) {
            $items[] = [
                'item_id' => $item->getSku(),
                'item_name' => $item->getName(),
                'price' => (float) number_format((float) $item->getPrice(), 2, '.', ''),
                'quantity' => (int) $item->getQty(),
            ];
        }

        $data = [
            'currency' => $this->_storeManager->getStore()->getCurrentCurrencyCode(),
            'value' => (float) number_format((float) $quote->getGrandTotal(), 2, '.', ''),
            'items' => $items,
        ];

        return $this->serializer->serialize($data);
    }

    public function isEnabled(): bool
    {
        return $this->config->isEnabled() && $this->config->isEventEnabled('begin_checkout');
    }
}
