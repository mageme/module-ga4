# Changelog

## 1.0.0 - 2026-04-06

### Added
- GA4 ecommerce event tracking via Cloudflare Zaraz (`zaraz.ecommerce()`)
- Events: `Purchase`, `Product Viewed`, `Add to Cart`, `Checkout Started`, `Product List Viewed`
- Server-side purchase tracking via GA4 Measurement Protocol (fallback)
- Breeze-compatible JS component (`tracker.js`)
- Admin configuration: enable/disable, Measurement ID, API Secret, server-side toggle, debug mode
- Debug mode with request/response logging to `var/log/mageme_ga4.log`
