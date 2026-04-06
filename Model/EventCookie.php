<?php

declare(strict_types=1);

namespace MageMe\GA4\Model;

use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;

/**
 * Stores pending GA4 events in a cookie so tracker.js can read them on the next page load.
 * This avoids FPC issues — cookie content is always fresh regardless of page cache.
 */
class EventCookie
{
    public const COOKIE_NAME = 'mgm_ga4_events';

    private CookieManagerInterface $cookieManager;
    private CookieMetadataFactory $cookieMetadataFactory;
    private SerializerInterface $serializer;

    public function __construct(
        CookieManagerInterface $cookieManager,
        CookieMetadataFactory $cookieMetadataFactory,
        SerializerInterface $serializer
    ) {
        $this->cookieManager = $cookieManager;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
        $this->serializer = $serializer;
    }

    public function addEvent(string $event, array $data): void
    {
        $existing = $this->getEvents();
        $existing[] = ['event' => $event, 'data' => $data];

        $metadata = $this->cookieMetadataFactory->createPublicCookieMetadata()
            ->setPath('/')
            ->setHttpOnly(false)
            ->setSecure(false)
            ->setDuration(60);

        $this->cookieManager->setPublicCookie(
            self::COOKIE_NAME,
            base64_encode($this->serializer->serialize($existing)),
            $metadata
        );
    }

    private function getEvents(): array
    {
        $value = $this->cookieManager->getCookie(self::COOKIE_NAME);
        if (!$value) {
            return [];
        }

        try {
            return $this->serializer->unserialize(base64_decode($value));
        } catch (\Exception $e) {
            return [];
        }
    }
}
