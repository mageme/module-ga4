<?php

declare(strict_types=1);

namespace MageMe\GA4\Block;

use Magento\Framework\View\Element\Template;

class Currency extends Template
{
    public function getCurrencyCode(): string
    {
        return $this->_storeManager->getStore()->getCurrentCurrencyCode();
    }
}
