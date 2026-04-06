<?php

declare(strict_types=1);

namespace MageMe\GA4\Observer;

use MageMe\GA4\Model\Config;
use MageMe\GA4\Service\MeasurementProtocol;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Psr\Log\LoggerInterface;

class SendMeasurementProtocolObserver implements ObserverInterface
{
    private Config $config;
    private MeasurementProtocol $measurementProtocol;
    private CookieManagerInterface $cookieManager;
    private LoggerInterface $logger;

    public function __construct(
        Config $config,
        MeasurementProtocol $measurementProtocol,
        CookieManagerInterface $cookieManager,
        LoggerInterface $logger
    ) {
        $this->config = $config;
        $this->measurementProtocol = $measurementProtocol;
        $this->cookieManager = $cookieManager;
        $this->logger = $logger;
    }

    public function execute(EventObserver $observer): void
    {
        if (!$this->config->isEnabled()
            || !$this->config->isEventEnabled('purchase')
            || !$this->config->isServerSidePurchaseEnabled()
            || !$this->config->getApiSecret()
        ) {
            return;
        }

        $orderIds = $observer->getEvent()->getOrderIds();
        if (empty($orderIds) || !is_array($orderIds)) {
            return;
        }

        $clientId = $this->extractClientId();

        foreach ($orderIds as $orderId) {
            try {
                $this->measurementProtocol->sendPurchase((int) $orderId, $clientId);
            } catch (\Exception $e) {
                $this->logger->error('[MageMe_GA4] Failed to send MP purchase', [
                    'order_id' => $orderId,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Extract client_id from _ga cookie.
     * Cookie format: GA1.1.XXXXXXXXX.XXXXXXXXX — client_id is the last two segments.
     * Falls back to a generated UUID if cookie is absent.
     */
    private function extractClientId(): string
    {
        $gaCookie = $this->cookieManager->getCookie('_ga');
        if ($gaCookie) {
            $parts = explode('.', $gaCookie);
            if (count($parts) >= 4) {
                return $parts[2] . '.' . $parts[3];
            }
        }

        // Fallback: generate a pseudo-random client_id
        return sprintf('%d.%d', random_int(1000000000, 9999999999), time());
    }
}
