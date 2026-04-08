<?php

declare(strict_types=1);

namespace MageMe\GA4\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Config
{
    private const XML_PATH_ACTIVE = 'mageme_ga4/general/active';
    private const XML_PATH_MEASUREMENT_ID = 'mageme_ga4/general/measurement_id';
    private const XML_PATH_API_SECRET = 'mageme_ga4/general/api_secret';
    private const XML_PATH_SERVER_SIDE_PURCHASE = 'mageme_ga4/general/server_side_purchase';
    private const XML_PATH_LOAD_GTAG = 'mageme_ga4/general/load_gtag';
    private const XML_PATH_ZARAZ_ECOMMERCE = 'mageme_ga4/general/zaraz_ecommerce';
    private const XML_PATH_DEBUG = 'mageme_ga4/general/debug';

    private ScopeConfigInterface $scopeConfig;

    public function __construct(ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    public function isEnabled(?int $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_ACTIVE, ScopeInterface::SCOPE_STORE, $storeId);
    }

    public function getMeasurementId(?int $storeId = null): string
    {
        return (string) $this->scopeConfig->getValue(self::XML_PATH_MEASUREMENT_ID, ScopeInterface::SCOPE_STORE, $storeId);
    }

    public function getApiSecret(?int $storeId = null): string
    {
        return (string) $this->scopeConfig->getValue(self::XML_PATH_API_SECRET, ScopeInterface::SCOPE_STORE, $storeId);
    }

    public function isServerSidePurchaseEnabled(?int $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_SERVER_SIDE_PURCHASE, ScopeInterface::SCOPE_STORE, $storeId);
    }

    public function isLoadGtagEnabled(?int $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_LOAD_GTAG, ScopeInterface::SCOPE_STORE, $storeId);
    }

    public function isZarazEcommerceEnabled(?int $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_ZARAZ_ECOMMERCE, ScopeInterface::SCOPE_STORE, $storeId);
    }

    public function isDebugMode(): bool
    {
        return $this->scopeConfig->isSetFlag(self::XML_PATH_DEBUG);
    }

    public function getEnabledEvents(?int $storeId = null): array
    {
        $value = (string) $this->scopeConfig->getValue(
            'mageme_ga4/general/enabled_events',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
        return $value !== '' ? explode(',', $value) : [];
    }

    public function isEventEnabled(string $eventName, ?int $storeId = null): bool
    {
        return in_array($eventName, $this->getEnabledEvents($storeId), true);
    }

    /**
     * Returns list of disabled JS-only events (add_to_cart, remove_from_cart, select_item)
     * for passing to frontend tracker.
     */
    public function getDisabledJsEvents(?int $storeId = null): array
    {
        $jsEvents = ['add_to_cart', 'remove_from_cart', 'select_item'];
        $enabled = $this->getEnabledEvents($storeId);
        return array_values(array_diff($jsEvents, $enabled));
    }
}
