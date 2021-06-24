<?php

namespace Redbox\GivexGiftCard\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;
use Magento\Sales\Model\Order;
use Redbox\GivexGiftCard\Helper\Data;

class ProcessOrderPlace implements ObserverInterface
{
    /**
     * Gift card account data
     *
     * @var Data2
     */
    protected $helper;


    public function __construct(
        Data $helper
    ) {
        $this->helper = $helper;
    }

    /**
     * Charge all gift cards applied to the order
     * used for event: sales_order_place_after
     *
     * @param Observer $observer
     * @return $this
     */
    public function execute(Observer $observer)
    {
        /** @var Order $order */
        $order = $observer->getEvent()->getOrder();
        /** @var Address $address */
        $address = $observer->getEvent()->getAddress();
        if (!$address) {
            // Single address checkout.
            /** @var Quote $quote */
            $quote = $observer->getEvent()->getQuote();
            $address = $quote->isVirtual() ? $quote->getBillingAddress() : $quote->getShippingAddress();
        }

        $order->setGivexGiftCards($address->getGivexGiftCards());
        $order->setGivexGiftCardsAmount($address->getGivexGiftCardsAmount());
        $order->setBaseGivexGiftCardsAmount($address->getGiveXBaseGiftCardsAmount());
        $cards = $this->helper->getCards($order);
        if (is_array($cards)) {
            $this->helper->setCards($order, $cards);
        }

        return $this;
    }
}
