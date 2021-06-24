<?php

namespace Redbox\GivexGiftCard\Api;

use Magento\Framework\Exception\NoSuchEntityException;
use Redbox\GivexGiftCard\Api\Data\GiftCardAccountInterface;

/**
 * Interface GuestGiftCardAccountManagementInterface
 * @api
 */
interface GuestGiftCardAccountManagementInterface
{
    /**
     * Add gift card to the cart.
     *
     * @param string $cartId
     * @param GiftCardAccountInterface $giftCardAccountData
     * @return bool
     */
    public function addGiftCard(
        $cartId,
        GiftCardAccountInterface $giftCardAccountData
    );

    /**
     * Check gift card balance if added to the cart.
     *
     * @param string $cartId
     * @param string $giftCardCode
     * @throws NoSuchEntityException
     * @throws TooManyAttemptsException
     * @return float
     */
    public function checkGiftCard($cartId, $giftCardCode);

    /**
     * Remove GiftCard Account entity.
     *
     * @param string $cartId
     * @param string $giftCardCode
     * @return bool
     * @since 100.1.0
     */
    public function deleteByQuoteId($cartId, $giftCardCode);
}
