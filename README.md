# MageMe GA4

[![Latest Version](https://img.shields.io/packagist/v/mageme/module-ga4.svg)](https://packagist.org/packages/mageme/module-ga4)
[![License: MIT](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)
[![Magento 2](https://img.shields.io/badge/Magento-2.4.6+-orange.svg)](https://magento.com)
[![PHP](https://img.shields.io/badge/PHP-8.1+-777BB4.svg)](https://php.net)

Lightweight GA4 ecommerce tracking for Magento 2 with deferred `gtag.js` loading for optimal PageSpeed.

Tracks 14 GA4 events client-side via `gtag.js`, with optional server-side purchase/refund tracking via the GA4 Measurement Protocol. CSP-compatible (nonce-based inline scripts).

## Requirements

- Magento 2.4.6+
- PHP 8.1+
- [Breeze Frontend](https://breezefront.com/) theme (checkout fallback via RequireJS also supported)

## Events

### Ecommerce

| Event | Trigger |
|-------|---------|
| Product Viewed | Product detail page |
| Product List Viewed | Category page |
| Product Clicked | Product link click in listing |
| Add to Cart | Any page (AJAX listener) |
| Remove from Cart | Minicart item removal |
| View Cart | Cart page |
| Checkout Started | Checkout page |
| Purchase | Order success page |
| Refund | Credit Memo creation (server-side via MP) |

### Other

| Event | Trigger |
|-------|---------|
| search | Catalog search results page |
| login | Customer login |
| sign_up | Customer registration |
| add_to_wishlist | Product added to wishlist |
| add_to_compare | Product added to comparison |

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
| Enabled Events | Multiselect — choose which GA4 events to track. Disable `page_view` if using Zaraz or GTM |

## How It Works

1. `gtag.js` loads with deferred strategy — on first user interaction (scroll, click, mousemove) or after 3.5 seconds, keeping PageSpeed scores high
2. Inline script uses CSP nonce via Magento's `SecureHtmlRenderer` — works on checkout and other CSP-restricted pages
3. Ecommerce events fire via `gtag('event', ...)` through Breeze components (category, product, cart pages) and RequireJS modules (checkout)
4. Post-redirect events (login, signup, wishlist, compare) are stored in a base64-encoded cookie and fired on the next page load

## Other MageMe Extensions

- [WebForms Pro](https://mageme.com/magento-2-form-builder.html) — Advanced form builder for Magento 2
- [WebForms Lite](https://mageme.com/free-magento-2-contact-form.html) — Free contact form for Magento 2
- [EasyQuote](https://mageme.com/magento-2-request-for-quote.html) — Request a quote directly from the shopping cart
- [HidePrice](https://mageme.com/magento-2-hide-price-extension.html) — Hide price & Add to Cart for guest visitors, with built-in quote request form

## License

[MIT](LICENSE)
