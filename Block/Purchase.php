<?php

declare(strict_types=1);

namespace MageMe\GA4\Block;

use MageMe\GA4\Model\Config;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Sales\Api\OrderRepositoryInterface;

class Purchase extends Template
{
    private Config $config;
    private OrderRepositoryInterface $orderRepository;
    private SearchCriteriaBuilder $searchCriteriaBuilder;
    private SerializerInterface $serializer;

    public function __construct(
        Context $context,
        Config $config,
        OrderRepositoryInterface $orderRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        SerializerInterface $serializer,
        array $data = []
    ) {
        $this->config = $config;
        $this->orderRepository = $orderRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->serializer = $serializer;
        parent::__construct($context, $data);
    }

    public function getTrackingData(): string
    {
        $orderIds = $this->getOrderIds();
        if (empty($orderIds) || !is_array($orderIds)) {
            return '';
        }

        $this->searchCriteriaBuilder->addFilter('entity_id', $orderIds, 'in');
        $collection = $this->orderRepository->getList($this->searchCriteriaBuilder->create());

        $result = [];
        foreach ($collection->getItems() as $order) {
            $items = [];
            foreach ($order->getAllVisibleItems() as $item) {
                $items[] = [
                    'item_id' => $item->getSku(),
                    'item_name' => $item->getName(),
                    'price' => (float) number_format((float) $item->getPrice(), 2, '.', ''),
                    'quantity' => (int) $item->getQtyOrdered(),
                ];
            }

            $result['orders'][] = [
                'transaction_id' => $order->getIncrementId(),
                'value' => (float) number_format((float) $order->getGrandTotal(), 2, '.', ''),
                'tax' => (float) number_format((float) $order->getTaxAmount(), 2, '.', ''),
                'shipping' => (float) number_format((float) $order->getShippingAmount(), 2, '.', ''),
                'currency' => $order->getOrderCurrencyCode(),
                'items' => $items,
            ];
        }

        return $result ? $this->serializer->serialize($result) : '';
    }

    public function isEnabled(): bool
    {
        return $this->config->isEnabled();
    }
}
