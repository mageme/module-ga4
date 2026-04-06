<?php

declare(strict_types=1);

namespace MageMe\GA4\Block;

use MageMe\GA4\Model\Config;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

class Currency extends Template
{
    private Config $config;

    public function __construct(Context $context, Config $config, array $data = [])
    {
        $this->config = $config;
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
}
