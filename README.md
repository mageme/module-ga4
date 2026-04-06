# MageMe GA4

[![Latest Version](https://img.shields.io/packagist/v/mageme/module-ga4.svg)](https://packagist.org/packages/mageme/module-ga4)
[![License: MIT](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)
[![Magento 2](https://img.shields.io/badge/Magento-2.4.6+-orange.svg)](https://magento.com)
[![PHP](https://img.shields.io/badge/PHP-8.1+-777BB4.svg)](https://php.net)

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

## Other MageMe Extensions

- [WebForms Pro](https://mageme.com/magento-2-form-builder.html) — Advanced form builder for Magento 2
- [WebForms Lite](https://mageme.com/free-magento-2-contact-form.html) — Free contact form for Magento 2
- [EasyQuote](https://mageme.com/magento-2-request-for-quote.html) — Request a quote directly from the shopping cart
- [HidePrice](https://mageme.com/magento-2-hide-price-extension.html) — Hide price & Add to Cart for guest visitors, with built-in quote request form

## License

[MIT](LICENSE)
