<?php

declare(strict_types=1);

namespace MageMe\GA4\Block;

use MageMe\GA4\Model\Config;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

class Search extends Template
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

    public function getTrackingData(): string
    {
        $query = $this->getRequest()->getParam('q');
        if (!$query) {
            return '';
        }

        return $this->serializer->serialize([
            'search_term' => $query,
        ]);
    }

    public function isEnabled(): bool
    {
        return $this->config->isEnabled();
    }
}
