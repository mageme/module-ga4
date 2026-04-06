<?php

declare(strict_types=1);

namespace MageMe\GA4\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class Events implements OptionSourceInterface
{
    public function toOptionArray(): array
    {
        return [
            ['value' => 'page_view', 'label' => __('page_view — Page view')],
            ['value' => 'view_item', 'label' => __('view_item — Product page')],
            ['value' => 'view_item_list', 'label' => __('view_item_list — Category / search listing')],
            ['value' => 'select_item', 'label' => __('select_item — Product click in listing')],
            ['value' => 'add_to_cart', 'label' => __('add_to_cart — Add to cart')],
            ['value' => 'remove_from_cart', 'label' => __('remove_from_cart — Remove from cart')],
            ['value' => 'view_cart', 'label' => __('view_cart — Cart page')],
            ['value' => 'begin_checkout', 'label' => __('begin_checkout — Checkout page')],
            ['value' => 'purchase', 'label' => __('purchase — Order success')],
            ['value' => 'search', 'label' => __('search — Search results')],
            ['value' => 'login', 'label' => __('login — Customer login')],
            ['value' => 'sign_up', 'label' => __('sign_up — Customer registration')],
            ['value' => 'add_to_wishlist', 'label' => __('add_to_wishlist — Add to wishlist')],
            ['value' => 'add_to_compare', 'label' => __('add_to_compare — Add to compare')],
        ];
    }
}
