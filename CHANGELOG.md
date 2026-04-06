# Changelog

## 1.0.0 - 2026-04-06

### Added
- GA4 ecommerce event tracking via Cloudflare Zaraz (`zaraz.ecommerce()`)
- Events: `Purchase`, `Product Viewed`, `Product Clicked`, `Add to Cart`, `Remove from Cart`, `View Cart`, `Checkout Started`, `Product List Viewed`, `Refund`
- Non-ecommerce events: `search`, `login`, `sign_up`, `add_to_wishlist`, `add_to_compare`
- Server-side tracking via GA4 Measurement Protocol (purchase, refund)
- Cookie-based event delivery for post-redirect actions (login, signup, wishlist, compare)
- Global currency detection from store config
- Breeze-compatible JS component (`tracker.js`)
- Admin configuration: enable/disable, Measurement ID, API Secret, server-side toggle, debug mode
- Debug mode with request/response logging to `var/log/mageme_ga4.log`
