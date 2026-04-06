# Changelog

## 1.0.0 - 2026-04-06

### Added
- GA4 ecommerce event tracking via deferred `gtag.js` loading
- Events: `purchase`, `view_item`, `select_item`, `add_to_cart`, `remove_from_cart`, `view_cart`, `begin_checkout`, `view_item_list`, `refund`
- Non-ecommerce events: `search`, `login`, `sign_up`, `add_to_wishlist`, `add_to_compare`
- Server-side tracking via GA4 Measurement Protocol (purchase, refund)
- Cookie-based event delivery for post-redirect actions (login, signup, wishlist, compare)
- Global currency detection from store config
- Deferred gtag.js loading (on user interaction or 3.5s timeout) for PageSpeed optimization
- CSP nonce support via `SecureHtmlRenderer` for checkout compatibility
- CSP whitelist for Google Analytics and Google Ads domains
- Breeze-compatible JS component with RequireJS fallback for checkout
- Admin configuration: enable/disable, Measurement ID, API Secret, server-side toggle, debug mode
- Debug mode with request/response logging to `var/log/mageme_ga4.log`
- Per-event toggle via multiselect in admin (enable/disable individual events including `page_view`)
