<?php

declare(strict_types=1);

namespace MageMe\GA4\Service;

use MageMe\GA4\Model\Config;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Psr\Log\LoggerInterface;

class MeasurementProtocol
{
    private const ENDPOINT = 'https://www.google-analytics.com/mp/collect';
    private const DEBUG_ENDPOINT = 'https://www.google-analytics.com/debug/mp/collect';

    private Config $config;
    private Curl $curl;
    private OrderRepositoryInterface $orderRepository;
    private SerializerInterface $serializer;
    private LoggerInterface $logger;

    public function __construct(
        Config $config,
        Curl $curl,
        OrderRepositoryInterface $orderRepository,
        SerializerInterface $serializer,
        LoggerInterface $logger
    ) {
        $this->config = $config;
        $this->curl = $curl;
        $this->orderRepository = $orderRepository;
        $this->serializer = $serializer;
        $this->logger = $logger;
    }

    /**
     * Send any GA4 event via Measurement Protocol.
     * Used for server-side events (refund, etc.) where no client_id is available.
     */
    public function sendEvent(string $eventName, array $params, ?string $clientId = null): void
    {
        $payload = [
            'client_id' => $clientId ?: ('server.' . time() . '.' . random_int(1000000, 9999999)),
            'events' => [
                [
                    'name' => $eventName,
                    'params' => $params,
                ],
            ],
        ];

        $this->post($payload, $params['transaction_id'] ?? $eventName);
    }

    public function sendPurchase(int $orderId, string $clientId): void
    {
        $order = $this->orderRepository->get($orderId);

        $items = [];
        foreach ($order->getAllVisibleItems() as $item) {
            $items[] = [
                'item_id' => $item->getSku(),
                'item_name' => $item->getName(),
                'price' => (float) number_format((float) $item->getPrice(), 2, '.', ''),
                'quantity' => (int) $item->getQtyOrdered(),
            ];
        }

        $payload = [
            'client_id' => $clientId,
            'events' => [
                [
                    'name' => 'purchase',
                    'params' => [
                        'transaction_id' => $order->getIncrementId(),
                        'value' => (float) number_format((float) $order->getGrandTotal(), 2, '.', ''),
                        'tax' => (float) number_format((float) $order->getTaxAmount(), 2, '.', ''),
                        'shipping' => (float) number_format((float) $order->getShippingAmount(), 2, '.', ''),
                        'currency' => $order->getOrderCurrencyCode(),
                        'items' => $items,
                    ],
                ],
            ],
        ];

        $this->post($payload, $order->getIncrementId());
    }

    private function post(array $payload, string $label): void
    {
        $measurementId = $this->config->getMeasurementId();
        $apiSecret = $this->config->getApiSecret();
        $isDebug = $this->config->isDebugMode();

        $baseUrl = $isDebug ? self::DEBUG_ENDPOINT : self::ENDPOINT;
        $url = $baseUrl . '?' . http_build_query([
            'measurement_id' => $measurementId,
            'api_secret' => $apiSecret,
        ]);

        $body = $this->serializer->serialize($payload);

        if ($isDebug) {
            $this->logger->info('[MageMe_GA4] MP Request', [
                'url' => $baseUrl,
                'measurement_id' => $measurementId,
                'label' => $label,
                'body' => $body,
            ]);
        }

        try {
            $this->curl->addHeader('Content-Type', 'application/json');
            $this->curl->post($url, $body);

            $status = $this->curl->getStatus();
            $response = $this->curl->getBody();

            if ($isDebug) {
                $this->logger->info('[MageMe_GA4] MP Response', [
                    'label' => $label,
                    'status' => $status,
                    'response' => $response,
                ]);
            }

            if ($status < 200 || $status >= 300) {
                $this->logger->error('[MageMe_GA4] MP request failed', [
                    'label' => $label,
                    'status' => $status,
                    'response' => $response,
                ]);
            }
        } catch (\Exception $e) {
            $this->logger->error('[MageMe_GA4] MP request exception', [
                'label' => $label,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
