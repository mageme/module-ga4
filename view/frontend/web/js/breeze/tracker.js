(function () {
    'use strict';

    const zarazReady = () => typeof zaraz !== 'undefined' && typeof zaraz.ecommerce === 'function';

    /**
     * Main tracker component — receives config from x-magento-init
     * config.event = 'Purchase' | 'Product Viewed' | 'Checkout Started' | 'Product List Viewed' | etc.
     * config.data  = event payload
     */
    $.breezemap['MageMe_GA4/js/tracker'] = (config) => {
        if (!zarazReady()) {
            return;
        }

        const { event, data } = config;

        if (event === 'Purchase' && data.orders) {
            data.orders.forEach((order) => zaraz.ecommerce('Purchase', order));
            return;
        }

        // Product List Viewed: data has eventData + productsMap
        if (event === 'Product List Viewed' && data.eventData) {
            window.mgmGa4Products = data.productsMap || {};
            window.mgmGa4Currency = data.eventData.currency || 'USD';
            zaraz.ecommerce('Product List Viewed', data.eventData);
            return;
        }

        // Search — zaraz.ecommerce expects GA4 event names
        if (event === 'search') {
            zaraz.ecommerce('search', data);
            return;
        }

        zaraz.ecommerce(event, data);
    };

    /**
     * add_to_cart — listen for Breeze's ajax:addToCart event (fires from any page)
     */
    $(document).on('ajax:addToCart', (e, eventData) => {
        if (!zarazReady()) {
            return;
        }

        const { sku, form } = eventData;
        let itemName = '';
        let price = 0;
        let quantity = 1;
        const currency = window.mgmGa4Currency || 'USD';

        // Strategy 1: data-ga4-product attribute (set by ViewItem block on PDP)
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

        // Strategy 2: global product map (set by ViewItemList on category pages)
        if (!itemName && window.mgmGa4Products) {
            const productIds = eventData.productIds || [];
            const productId = productIds[0];
            if (productId && window.mgmGa4Products[productId]) {
                const p = window.mgmGa4Products[productId];
                itemName = p.name || '';
                price = parseFloat(p.price) || 0;
            }
        }

        // Strategy 3: DOM scraping fallback (PDP only)
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

        zaraz.ecommerce('Add to Cart', {
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
        if (!zarazReady() || !window.mgmGa4Products) {
            return;
        }

        const href = e.currentTarget.href;
        if (!href) {
            return;
        }

        const products = window.mgmGa4Products;
        const match = Object.values(products).find((p) => href.includes(p.url) || p.url.includes(href));
        if (!match) {
            return;
        }

        zaraz.ecommerce('Product Clicked', {
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
     * Cookie-based events (login, sign_up, add_to_wishlist, add_to_compare).
     * Observers write JSON to mgm_ga4_events cookie, JS reads and clears it.
     */
    const cookieName = 'mgm_ga4_events';
    const cookieValue = $.cookies.get(cookieName);
    if (cookieValue && zarazReady()) {
        try {
            const events = JSON.parse(atob(cookieValue));
            if (Array.isArray(events)) {
                events.forEach((e) => zaraz.ecommerce(e.event, e.data));
            }
        } catch (err) { /* ignore malformed cookie */ }
        $.cookies.remove(cookieName, { path: '/' });
    }

    /**
     * remove_from_cart — listen for Breeze's ajax:removeFromCart event
     */
    $(document).on('ajax:removeFromCart', (e, eventData) => {
        if (!zarazReady()) {
            return;
        }

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

        zaraz.ecommerce('Product Removed', {
            currency,
            value,
            items,
        });
    });
})();
