# TODO

## Events

### Missing Ecommerce Events
- [x] `select_item` — product click (from category, search results, related/upsell/cross-sell)
- [x] `view_cart` — cart page view
- [x] `remove_from_cart` — item removed from cart
- [ ] `add_shipping_info` — shipping method selected at checkout
- [ ] `add_payment_info` — payment method selected at checkout
- [x] `refund` — triggered on Credit Memo creation in admin (server-side via MP)
- [ ] `view_promotion` — promotion banner impression
- [ ] `select_promotion` — promotion banner click

### Non-Ecommerce Events
- [x] `search` — catalog search query
- [x] `sign_up` — customer registration
- [x] `login` — customer login
- [x] `add_to_wishlist` — product added to wishlist
- [x] `add_to_compare` — product added to comparison

## Integrations
- [ ] Google Ads Conversion Tracking (AW-XXXXXXXXX)
- [ ] Google Ads Dynamic Remarketing
- [ ] Google Ads Enhanced Conversions (user-provided data)
- [ ] Facebook/Meta Pixel with Conversions API (CAPI)
- [ ] Google Consent Mode v2

## Server-Side Tracking
- [ ] Track admin-created orders via Measurement Protocol
- [ ] Track refunds server-side (Credit Memo observer)
- [ ] Fallback for ad-blocker bypass (send all events server-side when client blocked)

## Data Layer Enhancements
- [ ] Product brand/manufacturer attribute mapping
- [ ] Product variant tracking (configurable product options)
- [ ] Custom item-scoped dimensions (color, size, etc.)
- [ ] Category hierarchy in item_category / item_category2 / etc.
- [ ] Coupon code tracking in purchase event
- [ ] Product identification option: SKU vs Product ID (admin config)
- [ ] Revenue calculation options: include/exclude tax, shipping

## Luma Support
- [ ] RequireJS version of tracker.js
- [ ] requirejs-config.js for Luma theme compatibility

## Admin & UX
- [ ] Debug overlay on frontend (show fired events in browser console)
- [ ] Admin order tracking status (show if MP event was sent for each order)
- [ ] Exclude specific customer groups from tracking
- [ ] Exclude specific IP addresses from tracking
