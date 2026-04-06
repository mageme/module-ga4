define([], function () {
    'use strict';

    return function (config) {
        const track = (event, params) => {
            if (window.mgmGa4DisabledEvents?.includes(event)) return;
            if (typeof gtag === 'function') {
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
