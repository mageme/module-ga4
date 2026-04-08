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
        if ($clientId === null) {
            $this->logger->info(
                '[MageMe_GA4] Skipping MP purchase: no GA client_id (ad-blocker or missing cookie)',
                ['order_ids' => $orderIds]
            );
            return;
        }

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
     * Extract client_id from _ga cookie or our backup mgm_ga4_cid cookie.
     * Returns null if no valid client_id is available — server-side event
     * should be skipped to avoid orphaned transactions in GA4 funnels.
     */
    private function extractClientId(): ?string
    {
        // Primary: Google's _ga cookie (format GA1.1.XXXXXXXXX.XXXXXXXXX)
        $gaCookie = $this->cookieManager->getCookie('_ga');
        if ($gaCookie) {
            $parts = explode('.', $gaCookie);
            if (count($parts) >= 4) {
                return $parts[2] . '.' . $parts[3];
            }
        }

        // Fallback: our backup cookie set by tracker.js (survives payment redirects)
        $backupCid = $this->cookieManager->getCookie('mgm_ga4_cid');
        if ($backupCid && str_contains($backupCid, '.')) {
            return $backupCid;
        }

        return null;
    }
}
