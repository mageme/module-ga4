define([], function () {
    'use strict';

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

    const ZARAZ_TRACK_EVENTS = ['login', 'sign_up', 'add_to_compare', 'page_view'];

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

    return function (config) {
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
});
