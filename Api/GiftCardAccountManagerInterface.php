<?php

namespace Redbox\GivexGiftCard\Api;

use Redbox\GivexGiftCard\Api\Data\GiftCardDataInterface;

/**
 *
 * @author developer
 */
interface GiftCardAccountManagerInterface
{
    /**
     * @param GiftCardDataInterface $giftCardData
     * @return GiftCardDataInterface
     */
    public function requestByCode(GiftCardDataInterface $giftCardData);
}
