<?php

namespace Redbox\GivexGiftCard\Model;

use Magento\Framework\DataObject;
use Redbox\GivexGiftCard\Api\Data\GiftCardAccountInterface;

class GiftCardAccount extends DataObject implements GiftCardAccountInterface
{
    public function getGiftCards()
    {
        return $this->getData('gift_cards');
    }


    public function setGiftCards($cards)
    {
        $this->setData('gift_cards', $cards);
        return $this;
    }
}
