define([], function () {
    'use strict';

    return function (config) {
        var track = function (event, params) {
            if (typeof gtag === 'function') {
                gtag('event', event, params);
            }
        };

        var event = config.event;
        var data = config.data;

        if (event === 'Purchase' && data.orders) {
            data.orders.forEach(function (order) { track('purchase', order); });
            return;
        }

        if (event === 'Product List Viewed' && data.eventData) {
            window.mgmGa4Products = data.productsMap || {};
            window.mgmGa4Currency = data.eventData.currency || 'USD';
            track('view_item_list', data.eventData);
            return;
        }

        var eventMap = {
            'Product Viewed': 'view_item',
            'Checkout Started': 'begin_checkout',
            'view_cart': 'view_cart',
            'search': 'search'
        };

        track(eventMap[event] || event, data);
    };
});
