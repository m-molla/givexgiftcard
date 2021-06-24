<?php

namespace Redbox\GivexGiftCard\Model;

use Magento\Quote\Model\QuoteIdMaskFactory;
use Redbox\GivexGiftCard\Api\Data\GiftCardAccountInterface;
use Redbox\GivexGiftCard\Api\GiftCardAccountManagementInterface;
use Redbox\GivexGiftCard\Api\GuestGiftCardAccountManagementInterface;

class GuestGiftCardAccountManagement implements GuestGiftCardAccountManagementInterface
{
    /**
      * @var GiftCardAccountManagementInterface
      */
    protected $giftCartAccountManagement;

    /**
     * @var QuoteIdMaskFactory
     */
    protected $quoteIdMaskFactory;


    public function __construct(GiftCardAccountManagementInterface $giftCartAccountManagement, QuoteIdMaskFactory $quoteIdMaskFactory)
    {
        $this->giftCartAccountManagement = $giftCartAccountManagement;
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
    }

    public function addGiftCard($cartId, GiftCardAccountInterface $giftCardAccountData)
    {
        $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');
        return $this->giftCartAccountManagement->saveByQuoteId($quoteIdMask->getQuoteId(), $giftCardAccountData);
    }

    public function checkGiftCard($cartId, $giftCardCode): float
    {
        $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');
        return $this->giftCartAccountManagement->checkGiftCard($quoteIdMask->getQuoteId(), $giftCardCode);
    }

    public function deleteByQuoteId($cartId, $giftCardCode): bool
    {
        $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');
        return $this->giftCartAccountManagement->deleteByQuoteId($quoteIdMask->getQuoteId(), $giftCardCode);
    }
}
