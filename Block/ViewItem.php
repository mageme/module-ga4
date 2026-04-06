<?php

declare(strict_types=1);

namespace MageMe\GA4\Block;

use MageMe\GA4\Model\Config;
use Magento\Catalog\Model\Product;
use Magento\Framework\Registry;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

class ViewItem extends Template
{
    private Config $config;
    private Registry $registry;
    private SerializerInterface $serializer;

    public function __construct(
        Context $context,
        Config $config,
        Registry $registry,
        SerializerInterface $serializer,
        array $data = []
    ) {
        $this->config = $config;
        $this->registry = $registry;
        $this->serializer = $serializer;
        parent::__construct($context, $data);
    }

    public function getTrackingData(): string
    {
        $product = $this->getProduct();
        if (!$product) {
            return '';
        }

        $price = (float) number_format((float) $product->getFinalPrice(), 2, '.', '');
        $data = [
            'currency' => $this->getCurrencyCode(),
            'value' => $price,
            'items' => [
                [
                    'item_id' => $product->getSku(),
                    'item_name' => $product->getName(),
                    'price' => $price,
                ],
            ],
        ];

        return $this->serializer->serialize($data);
    }

    /**
     * Compact JSON for the data-ga4-product attribute on the add-to-cart form.
     * Used by tracker.js to enrich add_to_cart events with product name/price.
     */
    public function getProductJson(): string
    {
        $product = $this->getProduct();
        if (!$product) {
            return '{}';
        }

        return $this->serializer->serialize([
            'name' => $product->getName(),
            'price' => (float) number_format((float) $product->getFinalPrice(), 2, '.', ''),
        ]);
    }

    public function getCurrencyCode(): string
    {
        return $this->_storeManager->getStore()->getCurrentCurrencyCode();
    }

    public function isEnabled(): bool
    {
        return $this->config->isEnabled() && $this->config->isEventEnabled('view_item');
    }

    private function getProduct(): ?Product
    {
        return $this->registry->registry('current_product');
    }
}
