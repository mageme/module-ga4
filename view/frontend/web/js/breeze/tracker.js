(function () {
    'use strict';

    const track = (event, params) => {
        if (typeof gtag === 'function') {
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
