(function () {
    'use strict';

    // Backup GA client_id to own first-party cookie for server-side purchase tracking.
    // Survives payment redirects that may lose the _ga cookie.
    // Only relevant in gtag mode — Zaraz manages its own identity.
    if (window.mgmGa4TrackingMode !== 'zaraz') {
        const gaCookie = document.cookie.split('; ').find(c => c.startsWith('_ga='));
        if (gaCookie) {
            const parts = gaCookie.split('.');
            if (parts.length >= 4) {
                const clientId = parts.slice(-2).join('.');
                document.cookie = `mgm_ga4_cid=${clientId}; path=/; max-age=63072000; SameSite=Lax`;
            }
        }
    }

    // Zaraz ecommerce event name mapping (GA4 → Zaraz)
    const ZARAZ_ECOMMERCE_EVENTS = {
        'view_item':        'Product Viewed',
        'view_item_list':   'Product List Viewed',
        'select_item':      'Product Clicked',
        'add_to_cart':      'Product Added',
        'remove_from_cart': 'Product Removed',
        'view_cart':        'Cart Viewed',
        'begin_checkout':   'Checkout Started',
        'purchase':         'Order Completed',
        'search':           'Products Searched',
        'add_to_wishlist':  'Product Added to Wishlist',
    };

    // Non-ecommerce events use zaraz.track() instead of zaraz.ecommerce()
    const ZARAZ_TRACK_EVENTS = ['login', 'sign_up', 'add_to_compare', 'page_view'];

    /**
     * Transform GA4 params to Zaraz ecommerce format.
     * items → products, item_id → product_id, item_name → name, etc.
     */
    const toZarazParams = (event, params) => {
        if (!params || typeof params !== 'object') return params;

        const result = { ...params };

        if (result.items) {
            result.products = result.items.map(item => {
                const p = { ...item };
                if (p.item_id !== undefined) { p.product_id = p.item_id; delete p.item_id; }
                if (p.item_name !== undefined) { p.name = p.item_name; delete p.item_name; }
                return p;
            });
            delete result.items;
        }

        if (result.search_term !== undefined) {
            result.query = result.search_term;
            delete result.search_term;
        }

        if (event === 'purchase') {
            if (result.transaction_id !== undefined) { result.order_id = result.transaction_id; delete result.transaction_id; }
            if (result.value !== undefined) { result.revenue = result.value; delete result.value; }
        }

        return result;
    };

    const track = (event, params) => {
        if (window.mgmGa4DisabledEvents?.includes(event)) return;

        if (window.mgmGa4TrackingMode === 'zaraz') {
            if (typeof zaraz === 'undefined') return;
            if (ZARAZ_TRACK_EVENTS.includes(event)) {
                zaraz.track(event, params);
            } else {
                zaraz.ecommerce(ZARAZ_ECOMMERCE_EVENTS[event] || event, toZarazParams(event, params));
            }
        } else if (typeof gtag === 'function') {
            gtag('event', event, params);
        }
    };

    /**
     * Main tracker component — receives config from x-magento-init
     */
    $.breezemap['MageMe_GA4/js/tracker'] = (config) => {
        const { event, data } = config;

        if (event === 'Purchase' && data.orders) {
            data.orders.forEach((order) => track('purchase', order));
            return;
        }

        if (event === 'Product List Viewed' && data.eventData) {
            window.mgmGa4Products = data.productsMap || {};
            window.mgmGa4Currency = data.eventData.currency || 'USD';
            track('view_item_list', data.eventData);
            return;
        }

        const eventMap = {
            'Product Viewed': 'view_item',
            'Checkout Started': 'begin_checkout',
            'view_cart': 'view_cart',
            'search': 'search',
        };

        track(eventMap[event] || event, data);
    };

    /**
     * add_to_cart — listen for Breeze's ajax:addToCart event
     */
    $(document).on('ajax:addToCart', (e, eventData) => {
        const { sku, form } = eventData;
        let itemName = '';
        let price = 0;
        let quantity = 1;
        const currency = window.mgmGa4Currency || 'USD';

        const productForm = form?.length ? form : $(`[data-product-sku="${sku}"]`).closest('form');
        if (productForm.length) {
            const ga4Attr = productForm.attr('data-ga4-product');
            if (ga4Attr) {
                try {
                    const parsed = JSON.parse(ga4Attr);
                    itemName = parsed.name || '';
                    price = parseFloat(parsed.price) || 0;
                } catch (e) { /* ignore */ }
            }
            const qtyInput = productForm.find('input[name="qty"]');
            if (qtyInput.length) {
                quantity = parseInt(qtyInput.val(), 10) || 1;
            }
        }

        if (!itemName && window.mgmGa4Products) {
            const productIds = eventData.productIds || [];
            const productId = productIds[0];
            if (productId && window.mgmGa4Products[productId]) {
                const p = window.mgmGa4Products[productId];
                itemName = p.name || '';
                price = parseFloat(p.price) || 0;
            }
        }

        if (!itemName) {
            const heading = document.querySelector('.page-title-wrapper.product .page-title span');
            if (heading) {
                itemName = heading.textContent.trim();
            }
        }
        if (!price) {
            const priceEl = document.querySelector('[data-price-type="finalPrice"] .price');
            if (priceEl) {
                price = parseFloat(priceEl.textContent.replace(/[^0-9.]/g, '')) || 0;
            }
        }

        track('add_to_cart', {
            currency,
            value: price * quantity,
            items: [{
                item_id: sku || '',
                item_name: itemName,
                price,
                quantity,
            }],
        });
    });

    /**
     * select_item — product click in category/search listing
     */
    $(document).on('click', '.product-item a[href]', (e) => {
        if (!window.mgmGa4Products) {
            return;
        }

        const href = e.currentTarget.href;
        if (!href) {
            return;
        }

        const match = Object.values(window.mgmGa4Products).find((p) => href.includes(p.url) || p.url.includes(href));
        if (!match) {
            return;
        }

        track('select_item', {
            currency: window.mgmGa4Currency || 'USD',
            items: [{
                item_id: match.sku,
                item_name: match.name,
                price: match.price,
                index: match.index,
            }],
        });
    });

    /**
     * Cookie-based events (login, sign_up, add_to_wishlist, add_to_compare)
     */
    const cookieName = 'mgm_ga4_events';
    const cookieValue = $.cookies.get(cookieName);
    if (cookieValue) {
        try {
            const events = JSON.parse(atob(cookieValue));
            if (Array.isArray(events)) {
                events.forEach((e) => track(e.event, e.data));
            }
        } catch (err) { /* ignore malformed cookie */ }
        $.cookies.remove(cookieName, { path: '/' });
    }

    /**
     * remove_from_cart — listen for Breeze's ajax:removeFromCart event
     */
    $(document).on('ajax:removeFromCart', (e, eventData) => {
        const cartData = $.customerData.get('cart')();
        const productIds = eventData.productIds || [];
        const currency = window.mgmGa4Currency || cartData.currency || 'USD';
        const items = [];

        productIds.forEach((productId) => {
            const cartItem = (cartData.items || []).find(
                (item) => String(item.product_id) === String(productId)
            );
            if (cartItem) {
                items.push({
                    item_id: cartItem.product_sku || '',
                    item_name: cartItem.product_name || '',
                    price: parseFloat(cartItem.product_price_value) || 0,
                    quantity: parseInt(cartItem.qty, 10) || 1,
                });
            }
        });

        if (!items.length) {
            return;
        }

        const value = items.reduce((sum, item) => sum + item.price * item.quantity, 0);

        track('remove_from_cart', {
            currency,
            value,
            items,
        });
    });
})();
