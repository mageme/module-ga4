<?php

declare(strict_types=1);

namespace MageMe\GA4\Block;

use MageMe\GA4\Model\Config;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

class Init extends Template
{
    private Config $config;
    private SerializerInterface $serializer;

    public function __construct(
        Context $context,
        Config $config,
        SerializerInterface $serializer,
        array $data = []
    ) {
        $this->config = $config;
        $this->serializer = $serializer;
        parent::__construct($context, $data);
    }

    public function getCurrencyCode(): string
    {
        return $this->_storeManager->getStore()->getCurrentCurrencyCode();
    }

    public function getMeasurementId(): string
    {
        return $this->config->getMeasurementId();
    }

    /**
     * Returns JSON array of disabled JS-only events for the frontend tracker.
     */
    public function getDisabledEventsJson(): string
    {
        return $this->serializer->serialize($this->config->getDisabledJsEvents());
    }

    public function isSendPageView(): bool
    {
        return $this->config->isEventEnabled('page_view');
    }

    public function isLoadGtagEnabled(): bool
    {
        return $this->config->isLoadGtagEnabled();
    }

    public function isZarazEcommerceEnabled(): bool
    {
        return $this->config->isZarazEcommerceEnabled();
    }

    public function getTrackingMode(): string
    {
        return $this->config->isZarazEcommerceEnabled() ? 'zaraz' : 'gtag';
    }
}
