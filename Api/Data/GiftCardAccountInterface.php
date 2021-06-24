<?php

namespace Redbox\GivexGiftCard\Api\Data;

interface GiftCardAccountInterface
{
    /**
     * @return Redbox\GivexGiftCard\Api\Data\GiftCardDataInterface[]|null
     */
    public function getGiftCards();

    /**
     * @param Redbox\GivexGiftCard\Api\Data\GiftCardDataInterface[] $cards
     * @return $this;
     */
    public function setGiftCards($cards);
}
