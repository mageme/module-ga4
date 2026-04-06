<?php

declare(strict_types=1);

namespace MageMe\GA4\Block;

use MageMe\GA4\Model\Config;
use Magento\Catalog\Block\Product\ListProduct;
use Magento\Framework\Registry;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

class ViewItemList extends Template
{
    private const MAX_PRODUCTS = 20;

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
        /** @var ListProduct|null $listBlock */
        $listBlock = $this->getLayout()->getBlock('category.products.list');
        if (!$listBlock) {
            return '';
        }

        $collection = $listBlock->getLoadedProductCollection();
        if (!$collection || !$collection->count()) {
            return '';
        }

        $category = $this->registry->registry('current_category');
        $listName = $category ? $category->getName() : 'Category';

        $items = [];
        $productsMap = [];
        $index = 0;

        foreach ($collection as $product) {
            if ($index >= self::MAX_PRODUCTS) {
                break;
            }
            $price = (float) number_format((float) $product->getFinalPrice(), 2, '.', '');
            $items[] = [
                'item_id' => $product->getSku(),
                'item_name' => $product->getName(),
                'price' => $price,
                'index' => $index,
            ];
            $productsMap[$product->getId()] = [
                'name' => $product->getName(),
                'price' => $price,
                'sku' => $product->getSku(),
                'url' => $product->getProductUrl(),
                'index' => $index,
            ];
            $index++;
        }

        $data = [
            'item_list_name' => $listName,
            'currency' => $this->_storeManager->getStore()->getCurrentCurrencyCode(),
            'items' => $items,
        ];

        return $this->serializer->serialize([
            'eventData' => $data,
            'productsMap' => $productsMap,
        ]);
    }

    public function isEnabled(): bool
    {
        return $this->config->isEnabled();
    }
}
