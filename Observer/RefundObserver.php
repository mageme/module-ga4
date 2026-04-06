<?php

declare(strict_types=1);

namespace MageMe\GA4\Observer;

use MageMe\GA4\Model\Config;
use MageMe\GA4\Service\MeasurementProtocol;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class RefundObserver implements ObserverInterface
{
    private Config $config;
    private MeasurementProtocol $measurementProtocol;

    public function __construct(Config $config, MeasurementProtocol $measurementProtocol)
    {
        $this->config = $config;
        $this->measurementProtocol = $measurementProtocol;
    }

    public function execute(Observer $observer): void
    {
        if (!$this->config->isEnabled() || !$this->config->isServerSidePurchaseEnabled()) {
            return;
        }

        if (!$this->config->getApiSecret()) {
            return;
        }

        /** @var \Magento\Sales\Model\Order\Creditmemo $creditmemo */
        $creditmemo = $observer->getEvent()->getData('creditmemo');
        if (!$creditmemo) {
            return;
        }

        $order = $creditmemo->getOrder();

        $items = [];
        foreach ($creditmemo->getAllItems() as $item) {
            if ($item->getOrderItem()->getParentItemId()) {
                continue;
            }
            $items[] = [
                'item_id' => $item->getSku(),
                'item_name' => $item->getName(),
                'price' => (float) number_format((float) $item->getPrice(), 2, '.', ''),
                'quantity' => (int) $item->getQty(),
            ];
        }

        $this->measurementProtocol->sendEvent(
            'refund',
            [
                'transaction_id' => $order->getIncrementId(),
                'value' => (float) number_format((float) $creditmemo->getGrandTotal(), 2, '.', ''),
                'tax' => (float) number_format((float) $creditmemo->getTaxAmount(), 2, '.', ''),
                'shipping' => (float) number_format((float) $creditmemo->getShippingAmount(), 2, '.', ''),
                'currency' => $order->getOrderCurrencyCode(),
                'items' => $items,
            ]
        );
    }
}
