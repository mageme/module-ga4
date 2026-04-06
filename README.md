# MageMe GA4

GA4 ecommerce tracking for Magento 2 via Cloudflare Zaraz.

Sends ecommerce events using `zaraz.ecommerce()` on the client side, with optional server-side purchase tracking via the GA4 Measurement Protocol.

## Requirements

- Magento 2.4.6+
- PHP 8.1+
- Cloudflare Zaraz with GA4 tool configured (ecommerce enabled)
- [Breeze Frontend](https://breezefront.com/) theme

## Events

| Event | Page |
|-------|------|
| Product Viewed | Product detail page |
| Add to Cart | Any page (AJAX listener) |
| Product List Viewed | Category page |
| Checkout Started | Checkout page |
| Purchase | Order success page |

## Installation

```bash
composer require mageme/module-ga4
bin/magento module:enable MageMe_GA4
bin/magento setup:upgrade
```

## Configuration

**Stores > Configuration > MageMe > Google Analytics 4**

| Field | Description |
|-------|-------------|
| Enable | Enable/disable tracking |
| Measurement ID | GA4 Measurement ID (`G-XXXXXXXXXX`) |
| API Secret | Measurement Protocol API secret (for server-side tracking) |
| Server-side Purchase Tracking | Send purchase events via Measurement Protocol as fallback |
| Debug Mode | Log MP requests to `var/log/mageme_ga4.log` |

## Zaraz Setup

1. Add GA4 tool in Cloudflare Zaraz with your Measurement ID
2. Enable **E-commerce tracking** in the GA4 tool settings
3. The module calls `zaraz.ecommerce()` — no `gtag.js` or `dataLayer.push()` needed

## License

[MIT](LICENSE)
